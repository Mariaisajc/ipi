<?php
/**
 * User Controller
 * Gestión de usuarios del sistema
 */

class UserController extends Controller {
    
    private $userModel;
    private $businessModel;
    
    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->auth->check()) {
            $this->redirect('auth/login');
        }
        
        // Solo admins pueden gestionar usuarios
        $user = $this->auth->user();
        if ($user['role'] !== 'admin') {
            $this->redirect('admin/dashboard');
        }
        
        $this->userModel = $this->model('User');
        $this->businessModel = $this->model('Business');
    }
    
    /**
     * Listar usuarios
     */
    public function index() {
        // Gestionar estados automáticamente según fechas
        $stats = $this->userModel->manageUserStatusByDates();
        
        $page = $this->input('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Obtener filtros
        $search = $this->input('search');
        $role = $this->input('role');
        $status = $this->input('status');
        
        // Aplicar filtros
        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($role) $filters['role'] = $role;
        if ($status) $filters['status'] = $status;
        
        if (!empty($filters)) {
            $users = $this->userModel->filter($filters);
            $total = count($users);
            // Aplicar paginación manual
            $users = array_slice($users, $offset, $perPage);
        } else {
            $users = $this->userModel->getAll($perPage, $offset);
            $total = $this->userModel->countAll();
        }
        
        // Verificar qué usuarios pueden ser eliminados
        foreach ($users as &$user) {
            $user['can_delete_data'] = $this->userModel->canDelete($user['id']);
        }
        
        $totalPages = ceil($total / $perPage);
        
        $this->view('admin/users/index', [
            'title' => 'Usuarios',
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'role' => $role,
            'status' => $status
        ], 'admin');
    }
    
    /**
     * Mostrar formulario de creación
     */
    public function create() {
        // Obtener solo empresas activas para el selector
        $businesses = $this->businessModel->getActive();
        
        $this->view('admin/users/create', [
            'title' => 'Crear Usuario',
            'businesses' => $businesses
        ], 'admin');
    }
    
    /**
     * Guardar nuevo usuario
     */
    public function store() {
        // Validaciones
        $errors = [];
        
        $login = trim($this->input('login'));
        $name = trim($this->input('name'));
        $email = trim($this->input('email'));
        $password = $this->input('password');
        $password_confirm = $this->input('password_confirm');
        $role = $this->input('role');
        $business_id = $this->input('business_id');
        $start_date = $this->input('start_date');
        $end_date = $this->input('end_date');
        $status = $this->input('status');
        
        // Validar login
        if (empty($login)) {
            $errors[] = 'El login es obligatorio';
        } elseif ($this->userModel->loginExists($login)) {
            $errors[] = 'El login ya está en uso';
        }
        
        // Validar email (OBLIGATORIO)
        if (empty($email)) {
            $errors[] = 'El email es obligatorio';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        } elseif ($this->userModel->emailExists($email)) {
            $errors[] = 'El email ya está en uso';
        }
        
        // Validar contraseña
        if (empty($password)) {
            $errors[] = 'La contraseña es obligatoria';
        } elseif (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        } elseif ($password !== $password_confirm) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        // Validar rol
        if (empty($role) || !in_array($role, ['admin', 'encuestado'])) {
            $errors[] = 'Debe seleccionar un rol válido';
        }
        
        // Validar datos específicos de encuestado
        if ($role === 'encuestado') {
            if (empty($business_id)) {
                $errors[] = 'Los encuestados deben tener una empresa asignada';
            }
            if (empty($start_date)) {
                $errors[] = 'La fecha de inicio es obligatoria para encuestados';
            }
            if (empty($end_date)) {
                $errors[] = 'La fecha de fin es obligatoria para encuestados';
            }
            
            // Validar que las fechas sean >= hoy
            $today = date('Y-m-d');
            if (!empty($start_date) && $start_date < $today) {
                $errors[] = 'La fecha de inicio debe ser igual o posterior a hoy';
            }
            if (!empty($end_date) && $end_date < $today) {
                $errors[] = 'La fecha de fin debe ser igual o posterior a hoy';
            }
            if (!empty($start_date) && !empty($end_date) && $start_date > $end_date) {
                $errors[] = 'La fecha de fin debe ser posterior a la fecha de inicio';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => implode('<br>', $errors)
            ];
            $this->redirect('admin/users/create');
            return;
        }
        
        // Crear usuario
        $currentUser = $this->auth->user();
        
        $data = [
            'login' => $login,
            'name' => $name,
            'email' => $email,
            'password' => $this->userModel->hashPassword($password),
            'role' => $role,
            'business_id' => $role === 'encuestado' ? $business_id : null,
            'start_date' => $role === 'encuestado' ? $start_date : null,
            'end_date' => $role === 'encuestado' ? $end_date : null,
            'status' => $status ?? 'active',
            'created_by' => $currentUser['id']
        ];
        
        if ($this->userModel->create($data)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Usuario creado exitosamente'
            ];
            $this->redirect('admin/users');
        } else {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Error al crear el usuario'
            ];
            $this->redirect('admin/users/create');
        }
    }
    
    /**
     * Ver detalles de usuario
     */
    public function show() {
        $id = $this->input('id');
        
        if (!$id) {
            $this->redirect('admin/users');
            return;
        }
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Usuario no encontrado'
            ];
            $this->redirect('admin/users');
            return;
        }
        
        // Verificar si puede ser eliminado
        $canDeleteData = $this->userModel->canDelete($id);
        
        $this->view('admin/users/view', [
            'title' => 'Detalle de Usuario',
            'user' => $user,
            'canDeleteData' => $canDeleteData
        ], 'admin');
    }
    
    /**
     * Mostrar formulario de edición
     */
    public function edit() {
        $id = $this->input('id');
        
        if (!$id) {
            $this->redirect('admin/users');
            return;
        }
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Usuario no encontrado'
            ];
            $this->redirect('admin/users');
            return;
        }
        
        // Obtener solo empresas activas
        $businesses = $this->businessModel->getActive();
        
        $this->view('admin/users/edit', [
            'title' => 'Editar Usuario',
            'user' => $user,
            'businesses' => $businesses
        ], 'admin');
    }
    
    /**
     * Actualizar usuario
     */
    public function update() {
        $id = $this->input('id');
        
        if (!$id) {
            $this->redirect('admin/users');
            return;
        }
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Usuario no encontrado'
            ];
            $this->redirect('admin/users');
            return;
        }
        
        // Validaciones
        $errors = [];
        
        $login = trim($this->input('login'));
        $name = trim($this->input('name'));
        $email = trim($this->input('email'));
        $password = $this->input('password');
        $password_confirm = $this->input('password_confirm');
        $role = $this->input('role');
        $business_id = $this->input('business_id');
        $start_date = $this->input('start_date');
        $end_date = $this->input('end_date');
        $status = $this->input('status');
        
        // Validar login
        if (empty($login)) {
            $errors[] = 'El login es obligatorio';
        } elseif ($this->userModel->loginExists($login, $id)) {
            $errors[] = 'El login ya está en uso';
        }
        
        // Validar email (OBLIGATORIO)
        if (empty($email)) {
            $errors[] = 'El email es obligatorio';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        } elseif ($this->userModel->emailExists($email, $id)) {
            $errors[] = 'El email ya está en uso';
        }
        
        // Validar contraseña si se proporciona
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors[] = 'La contraseña debe tener al menos 6 caracteres';
            } elseif ($password !== $password_confirm) {
                $errors[] = 'Las contraseñas no coinciden';
            }
        }
        
        // Validar rol
        if (empty($role) || !in_array($role, ['admin', 'encuestado'])) {
            $errors[] = 'Debe seleccionar un rol válido';
        }
        
        // Validar datos específicos de encuestado
        if ($role === 'encuestado') {
            if (empty($business_id)) {
                $errors[] = 'Los encuestados deben tener una empresa asignada';
            }
            if (empty($start_date)) {
                $errors[] = 'La fecha de inicio es obligatoria para encuestados';
            }
            if (empty($end_date)) {
                $errors[] = 'La fecha de fin es obligatoria para encuestados';
            }
            if (!empty($start_date) && !empty($end_date) && $start_date > $end_date) {
                $errors[] = 'La fecha de fin debe ser posterior a la fecha de inicio';
            }
            
            // Validar que las fechas sean >= hoy
            $today = date('Y-m-d');
            if (!empty($start_date) && $start_date < $today) {
                $errors[] = 'La fecha de inicio debe ser igual o posterior a hoy';
            }
            if (!empty($end_date) && $end_date < $today) {
                $errors[] = 'La fecha de fin debe ser igual o posterior a hoy';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => implode('<br>', $errors)
            ];
            $this->redirect('admin/users/edit?id=' . $id);
            return;
        }
        
        // Actualizar usuario
        $data = [
            'login' => $login,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'business_id' => $role === 'encuestado' ? $business_id : null,
            'start_date' => $role === 'encuestado' ? $start_date : null,
            'end_date' => $role === 'encuestado' ? $end_date : null,
            'status' => $status ?? 'active'
        ];
        
        // Si hay nueva contraseña, hashearla
        if (!empty($password)) {
            $data['password'] = $this->userModel->hashPassword($password);
        }
        
        if ($this->userModel->update($id, $data)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Usuario actualizado exitosamente'
            ];
            $this->redirect('admin/users');
        } else {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Error al actualizar el usuario'
            ];
            $this->redirect('admin/users/edit?id=' . $id);
        }
    }
    
    /**
     * Eliminar usuario
     */
    public function destroy() {
        $id = $this->input('id');
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        // Verificar si puede ser eliminado
        $canDelete = $this->userModel->canDelete($id);
        
        if (!$canDelete['can_delete']) {
            echo json_encode(['success' => false, 'message' => $canDelete['reason']]);
            return;
        }
        
        if ($this->userModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario']);
        }
    }
}
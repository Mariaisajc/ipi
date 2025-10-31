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
        } elseif (strlen($login) < 3) {
            $errors[] = 'El login debe tener al menos 3 caracteres';
        } elseif (!preg_match('/^[a-z0-9_]+$/', $login)) {
            $errors[] = 'El login solo puede contener minúsculas, números y guión bajo (_)';
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
        // Obtener ID desde la URL (?id=X)
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$id) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'ID de usuario no proporcionado'
            ];
            $this->redirect('admin/users');
            return;
        }
        
        // Obtener usuario por ID
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
        // Obtener ID desde la URL (?id=X)
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$id) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'ID de usuario no proporcionado'
            ];
            $this->redirect('admin/users');
            return;
        }
        
        // Obtener usuario por ID
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
        if (!$this->isPost()) {
            $this->redirect('/admin/users');
        }

        $id = $this->input('id');
        $user = $this->userModel->getById($id);

        // Validaciones
        $login = $this->input('login');
        $name = $this->input('name');
        $email = $this->input('email');
        $password = $this->input('password');
        $password_confirm = $this->input('password_confirm');

        // Validar login
        if (empty($login)) {
            $errors[] = 'El login es obligatorio';
        } elseif (strlen($login) < 3) {
            $errors[] = 'El login debe tener al menos 3 caracteres';
        } elseif (!preg_match('/^[a-z0-9_]+$/', $login)) {
            $errors[] = 'El login solo puede contener minúsculas, números y guión bajo (_)';
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
        
        // No permitir cambiar el rol
        // No actualizar el estado si es encuestado (se gestiona por fechas)
        if ($user['role'] === 'admin') {
            // Regla #5: Proteger al super admin de ser inactivado
            if ($id == 1 && $this->input('status') === 'inactive') {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'El administrador principal no puede ser inactivado.'];
                $this->redirect('/admin/users/edit/' . $id);
                return;
            }
            $data['status'] = $this->input('status');
        }

        $data = [
            'login' => $login,
            'name' => $name,
            'email' => $email,
        ];
        
        if (!empty($this->input('password'))) {
            $data['password'] = $this->userModel->hashPassword($this->input('password'));
        }
        
        // Actualizar datos de encuestado si aplica
        if ($user['role'] === 'encuestado') {
            $data['business_id'] = $this->input('business_id');
            $data['start_date'] = $this->input('start_date');
            $data['end_date'] = $this->input('end_date');
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
     * Eliminar un usuario (AJAX)
     */
    public function delete() {
        header('Content-Type: application/json');
        
        if (!$this->isPost() || !$this->isAjax()) {
            echo json_encode(['success' => false, 'message' => 'Petición no válida']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
            return;
        }

        // Regla #5: Proteger al super admin
        if ($id == 1) {
            echo json_encode(['success' => false, 'message' => 'El administrador principal no puede ser eliminado.']);
            return;
        }
        
        // Verificar si el usuario actual intenta eliminarse a sí mismo
        if ($id == $this->auth->user()['id']) {
            echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta.']);
            return;
        }

        $deleteCheck = $this->userModel->canDelete($id);
        if (!$deleteCheck['can_delete']) {
            echo json_encode(['success' => false, 'message' => $deleteCheck['reason']]);
            return;
        }

        if ($this->userModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario de la base de datos.']);
        }
    }
    
    /**
     * Cambiar estado del usuario (activar/inactivar)
     * Permite cambiar estado de cualquier usuario
     */
    public function toggleStatus() {
        header('Content-Type: application/json');
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $newStatus = isset($_POST['status']) ? $_POST['status'] : null;
        
        // Validar ID
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        // Validar estado
        if (!in_array($newStatus, ['active', 'inactive'])) {
            echo json_encode(['success' => false, 'message' => 'Estado inválido']);
            return;
        }
        
        // Obtener usuario
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            return;
        }
        
        // No permitir desactivar al usuario conectado
        if ($id == $_SESSION['user_id'] && $newStatus === 'inactive') {
            echo json_encode([
                'success' => false, 
                'message' => 'No puedes desactivar tu propia cuenta'
            ]);
            return;
        }
        
        // Validar fechas para encuestados
        if ($user['role'] === 'encuestado' && !empty($user['start_date']) && !empty($user['end_date'])) {
            $today = date('Y-m-d');
            
            // No permitir activar si está fuera del período
            if ($newStatus === 'active') {
                if ($today < $user['start_date']) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se puede activar: El período de acceso aún no ha iniciado'
                    ]);
                    return;
                }
                
                if ($today > $user['end_date']) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se puede activar: El período de acceso ya expiró'
                    ]);
                    return;
                }
            }
        }
        
        // Actualizar estado
        $updated = $this->userModel->update($id, ['status' => $newStatus]);
        
        if ($updated) {
            $action = $newStatus === 'active' ? 'activado' : 'inactivado';
            echo json_encode([
                'success' => true, 
                'message' => "Usuario {$action} exitosamente"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado']);
        }
    }
    
    /**
     * Eliminar usuario (deprecado - mantenido para compatibilidad)
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
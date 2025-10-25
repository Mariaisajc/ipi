<?php
/**
 * Business Controller
 * Gestión de empresas
 */

class BusinessController extends Controller {
    
    private $businessModel;
    private $businessAreaModel;
    
    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->auth->check()) {
            $this->redirect('auth/login');
        }
        
        // Solo admins pueden gestionar empresas
        $user = $this->auth->user();
        if ($user['role'] !== 'admin') {
            $this->redirect('admin/dashboard');
        }
        
        $this->businessModel = $this->model('Business');
        $this->businessAreaModel = $this->model('BusinessArea');
    }
    
    /**
     * Listar empresas
     */
    public function index() {
        $page = $this->input('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Obtener filtros
        $search = $this->input('search');
        $status = $this->input('status');
        
        // Aplicar filtros
        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($status) $filters['status'] = $status;
        
        if (!empty($filters)) {
            $businesses = $this->businessModel->filter($filters);
            $total = count($businesses);
            // Aplicar paginación manual
            $businesses = array_slice($businesses, $offset, $perPage);
        } else {
            $businesses = $this->businessModel->getAll($perPage, $offset);
            $total = $this->businessModel->countAll();
        }
        
        $totalPages = ceil($total / $perPage);
        
        $this->view('admin/businesses/index', [
            'title' => 'Empresas',
            'businesses' => $businesses,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'status' => $status,
            'businessAreaModel' => $this->businessAreaModel
        ], 'admin');
    }
    
    /**
     * Mostrar formulario de creación
     */
    public function create() {
        $this->view('admin/businesses/create', [
            'title' => 'Nueva Empresa'
        ], 'admin');
    }
    
    /**
     * Guardar nueva empresa
     */
    public function store() {
        if (!$this->isPost()) {
            $this->redirect('admin/businesses');
            return;
        }
        
        // Validar CSRF
        if (!$this->validateCSRF()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Token de seguridad inválido'];
            $this->redirect('admin/businesses/create');
            return;
        }
        
        // Obtener datos
        $data = [
            'name' => trim($this->input('name')),
            'razon_social' => trim($this->input('razon_social')),
            'nit' => trim($this->input('nit')),
            'address' => trim($this->input('address')),
            'country' => trim($this->input('country')),
            'sector' => trim($this->input('sector')),
            'subsector' => trim($this->input('subsector')),
            'total_empleados' => $this->input('total_empleados'),
            'area_name' => trim($this->input('area_name')),
            'area_empleados' => $this->input('area_empleados'),
            'empleados_invitados' => $this->input('empleados_invitados'),
            'tiene_departamento_innovacion' => $this->input('tiene_departamento_innovacion'),
            'nivel_departamento_innovacion' => $this->input('nivel_departamento_innovacion'),
            'idiomas_participantes' => $this->input('idiomas_participantes') ? implode(',', $this->input('idiomas_participantes')) : null,
            'idioma_informe' => $this->input('idioma_informe'),
            'start_date' => $this->input('start_date'),
            'end_date' => $this->input('end_date'),
            'administrador_nombre' => trim($this->input('administrador_nombre')),
            'administrador_email' => trim($this->input('administrador_email')),
            'status' => $this->input('status') ?? 'active',
            'created_by' => $this->auth->id() ?? 1
        ];
        
        // Validar campos requeridos
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'El nombre de la empresa es requerido';
        } elseif ($this->businessModel->nameExists($data['name'])) {
            $errors[] = 'Ya existe una empresa con ese nombre';
        }
        
        if (!empty($data['administrador_email']) && !filter_var($data['administrador_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email del administrador no es válido';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            save_old($data);
            $this->redirect('admin/businesses/create');
            return;
        }
        
        // Crear empresa
        $businessId = $this->businessModel->create($data);
        
        if ($businessId) {
            // Crear áreas de negocio si se enviaron
            $areas = $this->input('areas');
            if (!empty($areas) && is_array($areas)) {
                foreach ($areas as $area) {
                    // Validar que sea un array con 'name'
                    if (is_array($area) && !empty($area['name'])) {
                        $areaName = trim($area['name']);
                        if (!empty($areaName)) {
                            $this->businessAreaModel->create([
                                'business_id' => $businessId,
                                'name' => $areaName,
                                'description' => !empty($area['description']) ? trim($area['description']) : null,
                                'created_by' => $this->auth->id() ?? 1
                            ]);
                        }
                    }
                    // Soporte para formato antiguo (solo string)
                    elseif (is_string($area)) {
                        $areaName = trim($area);
                        if (!empty($areaName)) {
                            $this->businessAreaModel->create([
                                'business_id' => $businessId,
                                'name' => $areaName,
                                'description' => null,
                                'created_by' => $this->auth->id() ?? 1
                            ]);
                        }
                    }
                }
            }
            
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Empresa creada exitosamente'];
            $this->redirect('admin/businesses');
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error al crear la empresa'];
            save_old($data);
            $this->redirect('admin/businesses/create');
        }
    }
    
    /**
     * Ver detalles de una empresa (solo lectura)
     */
    public function show() {
        $id = $this->input('id');
        
        if (!$id) {
            $this->redirect('admin/businesses');
            return;
        }
        
        $business = $this->businessModel->getById($id);
        
        if (!$business) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Empresa no encontrada'];
            $this->redirect('admin/businesses');
            return;
        }
        
        // Obtener áreas de negocio
        $areas = $this->businessAreaModel->getByBusiness($id);
        
        $this->view('admin/businesses/view', [
            'title' => 'Detalles de Empresa',
            'business' => $business,
            'areas' => $areas
        ], 'admin');
    }
    
    /**
     * Mostrar formulario de edición
     */
    public function edit() {
        $id = $this->input('id');
        
        if (!$id) {
            $this->redirect('admin/businesses');
            return;
        }
        
        $business = $this->businessModel->getById($id);
        
        if (!$business) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Empresa no encontrada'];
            $this->redirect('admin/businesses');
            return;
        }
        
        // Obtener áreas de negocio
        $areas = $this->businessAreaModel->getByBusiness($id);
        
        $this->view('admin/businesses/edit', [
            'title' => 'Editar Empresa',
            'business' => $business,
            'areas' => $areas
        ], 'admin');
    }
    
    /**
     * Actualizar empresa
     */
    public function update() {
        if (!$this->isPost()) {
            $this->redirect('admin/businesses');
            return;
        }
        
        // Validar CSRF
        if (!$this->validateCSRF()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Token de seguridad inválido'];
            $this->redirect('admin/businesses');
            return;
        }
        
        $id = $this->input('id');
        
        if (!$id) {
            $this->redirect('admin/businesses');
            return;
        }
        
        // Verificar que la empresa existe
        $business = $this->businessModel->getById($id);
        if (!$business) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Empresa no encontrada'];
            $this->redirect('admin/businesses');
            return;
        }
        
        // Obtener datos
        $data = [
            'name' => trim($this->input('name')),
            'razon_social' => trim($this->input('razon_social')),
            'nit' => trim($this->input('nit')),
            'address' => trim($this->input('address')),
            'country' => trim($this->input('country')),
            'sector' => trim($this->input('sector')),
            'subsector' => trim($this->input('subsector')),
            'total_empleados' => $this->input('total_empleados'),
            'area_name' => trim($this->input('area_name')),
            'area_empleados' => $this->input('area_empleados'),
            'empleados_invitados' => $this->input('empleados_invitados'),
            'tiene_departamento_innovacion' => $this->input('tiene_departamento_innovacion'),
            'nivel_departamento_innovacion' => $this->input('nivel_departamento_innovacion'),
            'idiomas_participantes' => $this->input('idiomas_participantes') ? implode(',', $this->input('idiomas_participantes')) : null,
            'idioma_informe' => $this->input('idioma_informe'),
            'start_date' => $this->input('start_date'),
            'end_date' => $this->input('end_date'),
            'administrador_nombre' => trim($this->input('administrador_nombre')),
            'administrador_email' => trim($this->input('administrador_email')),
            'status' => $this->input('status') ?? 'active'
        ];
        
        // Validar
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'El nombre de la empresa es requerido';
        } elseif ($this->businessModel->nameExists($data['name'], $id)) {
            $errors[] = 'Ya existe otra empresa con ese nombre';
        }
        
        if (!empty($data['administrador_email']) && !filter_var($data['administrador_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email del administrador no es válido';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            save_old($data);
            $this->redirect('admin/businesses/edit?id=' . $id);
            return;
        }
        
        // Actualizar empresa
        $this->businessModel->update($id, $data);
        
        // Actualizar áreas de negocio (preservando created_at)
        $areas = $this->input('areas');
        $existingAreas = $this->businessAreaModel->getByBusiness($id);
        
        // Crear un mapa de áreas existentes por nombre
        $existingAreasMap = [];
        foreach ($existingAreas as $existingArea) {
            $existingAreasMap[strtolower(trim($existingArea['name']))] = $existingArea;
        }
        
        // Procesar áreas del formulario
        $processedAreaNames = [];
        
        if (!empty($areas) && is_array($areas)) {
            foreach ($areas as $area) {
                // Validar que sea un array con 'name'
                if (is_array($area) && !empty($area['name'])) {
                    $areaName = trim($area['name']);
                    $areaNameLower = strtolower($areaName);
                    $areaDescription = !empty($area['description']) ? trim($area['description']) : null;
                    
                    if (!empty($areaName)) {
                        // Si el área ya existe, actualizarla
                        if (isset($existingAreasMap[$areaNameLower])) {
                            $existingAreaId = $existingAreasMap[$areaNameLower]['id'];
                            $this->businessAreaModel->update($existingAreaId, [
                                'name' => $areaName,
                                'description' => $areaDescription
                            ]);
                        } else {
                            // Si es nueva, crearla
                            $this->businessAreaModel->create([
                                'business_id' => $id,
                                'name' => $areaName,
                                'description' => $areaDescription,
                                'created_by' => $this->auth->id() ?? 1
                            ]);
                        }
                        $processedAreaNames[] = $areaNameLower;
                    }
                }
                // Soporte para formato antiguo (solo string)
                elseif (is_string($area)) {
                    $areaName = trim($area);
                    $areaNameLower = strtolower($areaName);
                    
                    if (!empty($areaName)) {
                        // Si el área ya existe, actualizarla
                        if (isset($existingAreasMap[$areaNameLower])) {
                            $existingAreaId = $existingAreasMap[$areaNameLower]['id'];
                            $this->businessAreaModel->update($existingAreaId, [
                                'name' => $areaName,
                                'description' => null
                            ]);
                        } else {
                            // Si es nueva, crearla
                            $this->businessAreaModel->create([
                                'business_id' => $id,
                                'name' => $areaName,
                                'description' => null,
                                'created_by' => $this->auth->id() ?? 1
                            ]);
                        }
                        $processedAreaNames[] = $areaNameLower;
                    }
                }
            }
        }
        
        // Eliminar áreas que ya no están en el formulario
        foreach ($existingAreas as $existingArea) {
            $existingNameLower = strtolower(trim($existingArea['name']));
            if (!in_array($existingNameLower, $processedAreaNames)) {
                $this->businessAreaModel->delete($existingArea['id']);
            }
        }
        
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Empresa actualizada exitosamente'];
        $this->redirect('admin/businesses');
    }
    
    /**
     * Eliminar empresa
     */
    public function delete() {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        // Validar CSRF
        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Token de seguridad inválido']);
            return;
        }
        
        $id = $this->input('id');
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        // Verificar que la empresa existe
        $business = $this->businessModel->getById($id);
        if (!$business) {
            $this->json(['success' => false, 'message' => 'Empresa no encontrada']);
            return;
        }
        
        // VALIDACIÓN DE SEGURIDAD 1: Solo borrador puede eliminarse
        if ($business['status'] !== 'borrador') {
            $this->json([
                'success' => false, 
                'message' => 'Solo se pueden eliminar empresas en estado Borrador. Esta empresa está en estado: ' . 
                            ($business['status'] === 'active' ? 'Activa' : 'Inactiva')
            ]);
            return;
        }
        
        // VALIDACIÓN DE SEGURIDAD 2: Verificar que no tenga respuestas asociadas
        // Cuando implementemos encuestas, descomentar esto:
        /*
        $hasResponses = $this->businessModel->hasResponses($id);
        if ($hasResponses) {
            $this->json([
                'success' => false, 
                'message' => 'No se puede eliminar esta empresa porque tiene respuestas de encuestas asociadas'
            ]);
            return;
        }
        */
        
        // Eliminar empresa
        if ($this->businessModel->delete($id)) {
            $this->json(['success' => true, 'message' => 'Empresa eliminada exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar la empresa']);
        }
    }
}
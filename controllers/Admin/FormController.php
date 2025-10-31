<?php
/**
 * Controlador: FormController
 * Gestiona las operaciones CRUD de formularios
 */

class FormController extends Controller {
    
    private $formModel;
    private $questionModel;
    private $questionTypeModel;
    
    public function __construct() {
        parent::__construct();
        $this->formModel = new Form();
        $this->questionModel = new Question();
        $this->questionTypeModel = new QuestionType();
    }
    
    /**
     * Listado de formularios
     */
    public function index() {
        // Verificar autenticación
        if (!$this->auth->check()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Obtener filtros
        $status = $this->input('status');
        $search = $this->input('search');
        
        // Obtener formularios
        if ($search) {
            $forms = $this->formModel->search($search);
        } elseif ($status) {
            $forms = $this->formModel->getByStatus($status);
        } else {
            $forms = $this->formModel->getAllWithCreator();
        }
        
        // Cargar vista
        $this->view('admin/forms/index', [
            'title' => 'Formularios',
            'forms' => $forms,
            'currentStatus' => $status,
            'searchTerm' => $search
        ],'admin');
    }
    
    /**
     * Mostrar formulario de creación
     */
    public function create() {
        if (!$this->auth->check()) {
            $this->redirect('auth/login');
            return;
        }
        
        $this->view('admin/forms/create', [
            'title' => 'Crear Formulario'
        ],'admin');
    }
    
    /**
     * Guardar nuevo formulario
     */
    public function store() {
        if (!$this->auth->check()) {
            $this->redirect('auth/login');
            return;
        }
        
        $this->validateCSRF();
        
        // Validaciones
        $errors = [];
        
        $title = trim($this->input('title'));
        $description = trim($this->input('description'));
        
        if (empty($title)) {
            $errors[] = 'El título es obligatorio';
        } elseif (strlen($title) > 100) {
            $errors[] = 'El título no puede exceder 100 caracteres';
        }
        
        if ($errors) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => implode('<br>', $errors)
            ];
            $this->redirect('admin/forms/create');
            return;
        }
        
        // Crear formulario
        $formId = $this->formModel->create([
            'title' => $title,
            'description' => $description,
            'status' => 'draft',
            'created_by' => $this->auth->user()['id']
        ]);
        
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Formulario creado exitosamente. Ahora puedes agregar preguntas.'
        ];
        
        // Redirigir al constructor
        $this->redirect('admin/forms/builder?id=' . $formId);
    }
    
    /**
     * Vista detalle del formulario
     */
    public function show() {
        if (!$this->auth->check()) {
            $this->redirect('auth/login');
            return;
        }
        
        $id = $this->input('id');
        
        if (!$id) {
            $this->redirect('admin/forms');
            return;
        }
        
        $form = $this->formModel->getByIdWithDetails($id);
        
        if (!$form) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Formulario no encontrado'
            ];
            $this->redirect('admin/forms');
            return;
        }
        
        // Obtener preguntas con opciones
        $questions = $this->questionModel->getFormQuestionsWithOptions($id);
        
        // Obtener estadísticas
        $statistics = $this->formModel->getStatistics($id);
        
        $this->view('admin/forms/view', [
            'title' => 'Detalle del Formulario',
            'form' => $form,
            'questions' => $questions,
            'statistics' => $statistics
        ],'admin');
    }
    
    /**
     * Mostrar formulario de edición (Asignación de Usuarios)
     */
    public function edit() {
        if (!$this->auth->check()) {
            $this->redirect('auth/login');
            return;
        }
        
        $id = $this->input('id');
        if (!$id) {
            $this->redirect('admin/forms');
            return;
        }
        
        $form = $this->formModel->getByIdWithDetails($id);
        if (!$form) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Formulario no encontrado'];
            $this->redirect('admin/forms');
            return;
        }

        // --- NUEVA VALIDACIÓN ---
        // No se puede editar/asignar si el formulario está cerrado.
        if ($form['status'] === 'closed') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'No se pueden asignar usuarios a un formulario cerrado.'];
            $this->redirect('admin/forms/show?id=' . $id);
            return;
        }

        // Cargar el modelo de usuario si no existe
        if (!isset($this->userModel)) {
            $this->userModel = $this->model('User');
        }

        // --- CAMBIO DE MÉTODO ---
        // Obtener TODOS los usuarios 'encuestados' y los que ya están asignados a este formulario
        $availableUsers = $this->userModel->getAllUsersByRole('encuestado');
        $assignedUsers = $this->userModel->getAssignedUsersByForm($id);
        $assignedUserIds = array_column($assignedUsers, 'id');

        $this->view('admin/forms/edit', [
            'title' => 'Asignar Usuarios al Formulario',
            'form' => $form,
            'availableUsers' => $availableUsers,
            'assignedUserIds' => $assignedUserIds
        ],'admin');
    }
    
    /**
     * Actualizar asignaciones de usuarios
     */
    public function update() {
        if (!$this->isPost()) {
            $this->redirect('admin/forms');
            return;
        }
        
        $this->validateCSRF();
        
        $formId = $this->input('form_id');
        $title = trim($this->input('title')); // <-- NUEVO
        $selectedUserIds = $this->input('user_ids') ?? [];

        if (!$formId || empty($title)) { // <-- MODIFICADO
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'El ID y el título del formulario son obligatorios.'];
            $this->redirect($formId ? 'admin/forms/edit?id=' . $formId : 'admin/forms');
            return;
        }

        // Cargar el modelo de usuario si no existe
        if (!isset($this->userModel)) {
            $this->userModel = $this->model('User');
        }

        try {
            // --- NUEVA LÓGICA ---
            // 1. Actualizar el título del formulario
            $this->formModel->update($formId, ['title' => $title]);

            // 2. Sincronizar usuarios
            $this->formModel->syncAssignedUsers($formId, $selectedUserIds, $this->auth->user()['id']);

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Formulario y asignaciones actualizadas correctamente.'
            ];

        } catch (Exception $e) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Ocurrió un error al actualizar las asignaciones: ' . $e->getMessage()
            ];
        }
        
        // Redirigir de vuelta a la página de asignación
        $this->redirect('admin/forms/edit?id=' . $formId);
    }
    
    /**
     * Eliminar formulario
     */
    public function destroy() {
        if (!$this->auth->check()) {
            $this->json(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        $this->validateCSRF();
        
        $id = $this->input('id');
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        
        $form = $this->formModel->getById($id);
        
        if (!$form) {
            $this->json(['success' => false, 'message' => 'Formulario no encontrado']);
            return;
        }
        
        // Verificar si puede eliminarse
        if (!$this->formModel->canDelete($id)) {
            $this->json([
                'success' => false, 
                'message' => 'No se puede eliminar el formulario porque tiene respuestas registradas'
            ]);
            return;
        }
        
        // Eliminar
        if ($this->formModel->delete($id)) {
            $this->json([
                'success' => true, 
                'message' => 'Formulario eliminado exitosamente'
            ]);
        } else {
            $this->json([
                'success' => false, 
                'message' => 'Error al eliminar el formulario'
            ]);
        }
    }
    
    /**
     * Vista constructor de formularios (drag-and-drop)
     */
    public function builder() {
        if (!$this->auth->check()) {
            $this->redirect('auth/login');
            return;
        }
        
        $id = $this->input('id');
        
        if (!$id) {
            $this->redirect('admin/forms');
            return;
        }
        
        $form = $this->formModel->getByIdWithDetails($id);
        
        if (!$form) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Formulario no encontrado'
            ];
            $this->redirect('admin/forms');
            return;
        }
        
        // Obtener preguntas con opciones
        $questions = $this->questionModel->getFormQuestionsWithOptions($id);
        
        // Obtener tipos de preguntas
        $questionTypes = $this->questionTypeModel->getAll();
        
        $this->view('admin/forms/builder', [
            'title' => 'Constructor de Formulario',
            'form' => $form,
            'questions' => $questions,
            'questionTypes' => $questionTypes
        ],'admin');
    }
    
    /**
     * Publicar formulario (MODIFICADO para envío de formulario normal)
     */
    public function publish() {
        if (!$this->isPost()) {
            $this->redirect('admin/forms');
            return;
        }

        $this->validateCSRF();
        $id = $this->input('id');

        if (!$id) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID de formulario no válido.'];
            $this->redirect('admin/forms');
            return;
        }

        // --- CORRECCIÓN ---
        // El método correcto es 'getByIdWithDetails', no 'getById'.
        $form = $this->formModel->getByIdWithDetails($id);
        if (!$form) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Formulario no encontrado.'];
            $this->redirect('admin/forms');
            return;
        }

        if (!$this->formModel->hasQuestions($id)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'El formulario debe tener al menos una pregunta para ser publicado.'];
            $this->redirect('admin/forms/builder?id=' . $id);
            return;
        }

        if ($this->formModel->publish($id)) {
            $_SESSION['flash'] = [
                'type' => 'success', 
                'message' => '¡Formulario publicado! Ahora está activo y listo para asignar a usuarios.'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'error', 
                'message' => 'Error al intentar publicar el formulario en la base de datos.'
            ];
        }
        
        $this->redirect('admin/forms');
    }
    
    /**
     * Cerrar formulario (MODIFICADO para envío de formulario normal)
     */
    public function close() {
        if (!$this->isPost()) {
            $this->redirect('admin/forms');
            return;
        }

        $this->validateCSRF();
        $id = $this->input('id');

        if (!$id) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID de formulario no válido.'];
            $this->redirect('admin/forms');
            return;
        }

        $form = $this->formModel->getByIdWithDetails($id);
        if (!$form) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Formulario no encontrado.'];
            $this->redirect('admin/forms');
            return;
        }

        if ($this->formModel->close($id)) {
            $_SESSION['flash'] = [
                'type' => 'success', 
                'message' => 'El formulario ha sido cerrado y ya no acepta nuevas respuestas.'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'error', 
                'message' => 'Error al intentar cerrar el formulario.'
            ];
        }
        
        $this->redirect('admin/forms');
    }
    
    /**
     * Duplicar formulario
     */
    public function duplicate() {
        if (!$this->auth->check()) {
            $this->json(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        $this->validateCSRF();
        
        $id = $this->input('id');
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        
        $form = $this->formModel->getById($id);
        
        if (!$form) {
            $this->json(['success' => false, 'message' => 'Formulario no encontrado']);
            return;
        }
        
        // Duplicar
        $newFormId = $this->formModel->duplicate($id, $this->auth->user()['id']);
        
        if ($newFormId) {
            $this->json([
                'success' => true, 
                'message' => 'Formulario duplicado exitosamente',
                'form_id' => $newFormId
            ]);
        } else {
            $this->json([
                'success' => false, 
                'message' => 'Error al duplicar el formulario'
            ]);
        }
    }

    /**
     * Obtiene una lista de preguntas de un formulario para usar en lógica condicional.
     * Excluye una pregunta específica si se proporciona.
     * Responde vía AJAX.
     */
    public function getQuestionsForLinking($formId) {
        if (!$this->isAjax()) {
            $this->forbidden();
        }

        $excludedQuestionId = $_GET['exclude'] ?? null;

        if (!$this->questionModel) {
            $this->questionModel = new Question();
        }

        $questions = $this->questionModel->getByFormId($formId);

        // Filtrar la pregunta excluida
        if ($excludedQuestionId) {
            $questions = array_filter($questions, function($q) use ($excludedQuestionId) {
                return $q['id'] != $excludedQuestionId;
            });
        }

        // Devolver solo los campos necesarios para reducir el tamaño de la respuesta
        $result = array_map(function($q) {
            return [
                'id' => $q['id'],
                'question_text' => $q['question_text']
            ];
        }, $questions);

        $this->json(array_values($result)); // Re-indexar array después de filter
    }

    /**
     * Cambiar el estado de un formulario (borrador, activo, cerrado)
     */
    public function changeStatus() {
        if (!$this->auth->check()) {
            $this->json(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        $this->validateCSRF();
        
        $id = $this->input('id');
        $status = $this->input('status');
        
        if (!$id || !$status) {
            $this->json(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        // Validar estado
        $validStatuses = ['draft', 'active', 'closed'];
        if (!in_array($status, $validStatuses)) {
            $this->json(['success' => false, 'message' => 'Estado no válido']);
            return;
        }
        
        $form = $this->formModel->getById($id);
        
        if (!$form) {
            $this->json(['success' => false, 'message' => 'Formulario no encontrado']);
            return;
        }
        
        // Si se va a activar, verificar que tenga preguntas
        if ($status === 'active' && !$this->formModel->hasQuestions($id)) {
            $this->json([
                'success' => false, 
                'message' => 'El formulario debe tener al menos una pregunta'
            ]);
            return;
        }
        
        // Cambiar estado
        if ($this->formModel->updateStatus($id, $status)) {
            $statusNames = [
                'draft' => 'borrador',
                'active' => 'activo',
                'closed' => 'cerrado'
            ];
            
            $this->json([
                'success' => true, 
                'message' => 'Estado cambiado a ' . $statusNames[$status]
            ]);
        } else {
            $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado'
            ]);
        }
    }
}
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
     * Mostrar formulario de edición
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
        
        $form = $this->formModel->getById($id);
        
        if (!$form) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Formulario no encontrado'
            ];
            $this->redirect('admin/forms');
            return;
        }
        
        $this->view('admin/forms/edit', [
            'title' => 'Editar Formulario',
            'form' => $form
        ],'admin');
    }
    
    /**
     * Actualizar formulario
     */
    public function update() {
        if (!$this->auth->check()) {
            $this->redirect('auth/login');
            return;
        }
        
        $this->validateCSRF();
        
        $id = $this->input('id');
        
        if (!$id) {
            $this->redirect('admin/forms');
            return;
        }
        
        $form = $this->formModel->getById($id);
        
        if (!$form) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Formulario no encontrado'
            ];
            $this->redirect('admin/forms');
            return;
        }
        
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
            $this->redirect('admin/forms/edit/' . $id);
            return;
        }
        
        // Actualizar
        $this->formModel->update($id, [
            'title' => $title,
            'description' => $description
        ]);
        
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Formulario actualizado exitosamente'
        ];
        
        $this->redirect('admin/forms/view/' . $id);
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
     * Publicar formulario (AJAX)
     */
    public function publish() {
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
        
        // Verificar que tenga preguntas
        if (!$this->formModel->hasQuestions($id)) {
            $this->json([
                'success' => false, 
                'message' => 'El formulario debe tener al menos una pregunta para ser publicado'
            ]);
            return;
        }
        
        // Publicar
        if ($this->formModel->publish($id)) {
            $this->json([
                'success' => true, 
                'message' => 'Formulario publicado exitosamente. Ahora está disponible para asignar.'
            ]);
        } else {
            $this->json([
                'success' => false, 
                'message' => 'Error al publicar el formulario'
            ]);
        }
    }
    
    /**
     * Cerrar formulario (AJAX)
     */
    public function close() {
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
        
        // Cerrar
        if ($this->formModel->close($id)) {
            $this->json([
                'success' => true, 
                'message' => 'Formulario cerrado. Ya no acepta nuevas respuestas.'
            ]);
        } else {
            $this->json([
                'success' => false, 
                'message' => 'Error al cerrar el formulario'
            ]);
        }
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
     * Cambiar estado del formulario (AJAX)
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
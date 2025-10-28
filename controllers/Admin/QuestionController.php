<?php
/**
 * Controlador: QuestionController
 * Gestiona las operaciones AJAX de preguntas
 */

class QuestionController extends Controller {
    
    private $questionModel;
    private $questionOptionModel;
    
    public function __construct() {
        parent::__construct();
        $this->questionModel = new Question();
        $this->questionOptionModel = new QuestionOption();
    }
    
    /**
     * Guardar nueva pregunta (AJAX)
     */
    public function store() {
        if (!$this->auth->check()) {
            $this->json(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        // Obtener datos JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar CSRF
        if (!isset($input['csrf_token']) || !$this->csrf->validate($input['csrf_token'])) {
            $this->json(['success' => false, 'message' => 'Token CSRF inválido']);
            return;
        }
        
        // Validaciones
        $errors = [];
        
        if (empty($input['form_id'])) {
            $errors[] = 'ID de formulario es obligatorio';
        }
        
        if (empty($input['question_text'])) {
            $errors[] = 'El texto de la pregunta es obligatorio';
        }
        
        if (empty($input['type_id'])) {
            $errors[] = 'El tipo de pregunta es obligatorio';
        }
        
        if ($errors) {
            $this->json(['success' => false, 'message' => implode(', ', $errors)]);
            return;
        }
        
        // Crear pregunta
        try {
            $questionId = $this->questionModel->create([
                'form_id' => $input['form_id'],
                'question_text' => $input['question_text'],
                'type_id' => $input['type_id'],
                'required' => $input['required'] ?? 0,
                'placeholder' => $input['placeholder'] ?? null,
                'help_text' => $input['help_text'] ?? null,
                'created_by' => $this->auth->user()['id']
            ]);
            
            // Si tiene opciones, crearlas
            if (isset($input['options']) && !empty($input['options'])) {
                foreach ($input['options'] as $index => $optionText) {
                    $this->questionOptionModel->create([
                        'question_id' => $questionId,
                        'option_text' => $optionText,
                        'value' => $optionText,
                        'order_number' => $index + 1
                    ]);
                }
            }
            
            $this->json([
                'success' => true,
                'message' => 'Pregunta creada exitosamente',
                'question_id' => $questionId
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error al crear la pregunta: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Actualizar pregunta (AJAX)
     */
    public function update() {
        if (!$this->auth->check()) {
            $this->json(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        // Obtener ID de la URL
        $id = $this->input('id');
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        
        // Obtener datos JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar CSRF
        if (!isset($input['csrf_token']) || !$this->csrf->validate($input['csrf_token'])) {
            $this->json(['success' => false, 'message' => 'Token CSRF inválido']);
            return;
        }
        
        try {
            // Actualizar pregunta
            $this->questionModel->update($id, [
                'question_text' => $input['question_text'],
                'type_id' => $input['type_id'],
                'required' => $input['required'] ?? 0,
                'placeholder' => $input['placeholder'] ?? null,
                'help_text' => $input['help_text'] ?? null
            ]);
            
            // Actualizar opciones si las tiene
            if (isset($input['options']) && !empty($input['options'])) {
                // Eliminar opciones existentes
                $this->questionOptionModel->deleteByQuestionId($id);
                
                // Crear nuevas opciones
                foreach ($input['options'] as $index => $optionText) {
                    $this->questionOptionModel->create([
                        'question_id' => $id,
                        'option_text' => $optionText,
                        'value' => $optionText,
                        'order_number' => $index + 1
                    ]);
                }
            }
            
            $this->json([
                'success' => true,
                'message' => 'Pregunta actualizada exitosamente'
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error al actualizar la pregunta: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Eliminar pregunta (AJAX)
     */
    public function delete() {
        if (!$this->auth->check()) {
            $this->json(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        // Obtener ID de la URL
        $id = $this->input('id');
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        
        // Obtener datos JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar CSRF
        if (!isset($input['csrf_token']) || !$this->csrf->validate($input['csrf_token'])) {
            $this->json(['success' => false, 'message' => 'Token CSRF inválido']);
            return;
        }
        
        // Verificar si puede eliminarse
        if (!$this->questionModel->canDelete($id)) {
            $this->json([
                'success' => false,
                'message' => 'No se puede eliminar la pregunta porque tiene respuestas registradas'
            ]);
            return;
        }
        
        try {
            $this->questionModel->delete($id);
            
            $this->json([
                'success' => true,
                'message' => 'Pregunta eliminada exitosamente'
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error al eliminar la pregunta: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Reordenar preguntas (AJAX)
     */
    public function reorder() {
        if (!$this->auth->check()) {
            $this->json(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        // Obtener datos JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar CSRF
        if (!isset($input['csrf_token']) || !$this->csrf->validate($input['csrf_token'])) {
            $this->json(['success' => false, 'message' => 'Token CSRF inválido']);
            return;
        }
        
        if (empty($input['form_id']) || empty($input['orders'])) {
            $this->json(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        try {
            $this->questionModel->reorder($input['form_id'], $input['orders']);
            
            $this->json([
                'success' => true,
                'message' => 'Orden actualizado'
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error al reordenar: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtener pregunta por ID (AJAX)
     */
    public function getQuestion() {
        if (!$this->auth->check()) {
            $this->json(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        $id = $this->input('id');
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        
        try {
            $question = $this->questionModel->getWithOptions($id);
            
            if (!$question) {
                $this->json(['success' => false, 'message' => 'Pregunta no encontrada']);
                return;
            }
            
            $this->json([
                'success' => true,
                'question' => $question
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
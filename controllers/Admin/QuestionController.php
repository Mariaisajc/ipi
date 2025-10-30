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
        $this->questionModel = $this->model('Question');
        $this->questionOptionModel = $this->model('QuestionOption');
    }

    private function validateRequest() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición no válida'], 400);
            return false;
        }
        if (!$this->auth->check()) {
            $this->json(['success' => false, 'message' => 'No autenticado'], 401);
            return false;
        }
        return true;
    }
    
    /**
     * Guardar nueva pregunta (AJAX)
     */
    public function store() {
        if (!$this->validateRequest()) return;
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->csrf->validate($input['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            return;
        }
        
        if (empty($input['question_text'])) {
            $this->json(['success' => false, 'message' => 'El texto de la pregunta es obligatorio'], 422);
            return;
        }

        // Obtener la conexión a la BD de forma centralizada
        $db = Model::getDbConnection();
        try {
            $db->beginTransaction();

            $questionId = $this->questionModel->create([
                'form_id' => $input['form_id'],
                'question_text' => $input['question_text'],
                'type_id' => $input['type_id'],
                'required' => $input['required'] ?? 0,
                'placeholder' => $input['placeholder'] ?? null,
                'help_text' => $input['help_text'] ?? null,
                'created_by' => $this->auth->user()['id']
            ], $db); // Pasar la conexión
            
            if (isset($input['options']) && is_array($input['options'])) {
                $this->syncOptionsAndConditionals($questionId, $input['options'], $db); // Pasar la conexión
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Pregunta creada exitosamente', 'question_id' => $questionId]);
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Error al crear pregunta: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno al crear la pregunta.'], 500);
        }
    }
    
    /**
     * Actualizar pregunta (AJAX)
     */
    public function update() {
        if (!$this->validateRequest()) return;

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID de pregunta no proporcionado'], 400);
            return;
        }
        if (!$this->csrf->validate($input['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            return;
        }

        $db = Model::getDbConnection();
        try {
            $db->beginTransaction();

            $dataToUpdate = [];
            $allowedFields = ['question_text', 'required', 'placeholder', 'help_text'];
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $input)) {
                    $dataToUpdate[$field] = $input[$field];
                }
            }

            if (!empty($dataToUpdate)) {
                $this->questionModel->update($id, $dataToUpdate, $db); // Pasar la conexión
            }
            
            if (isset($input['options']) && is_array($input['options'])) {
                $this->syncOptionsAndConditionals($id, $input['options'], $db); // Pasar la conexión
            } else {
                // Si se cambia de un tipo con opciones a uno sin opciones,
                // el array 'options' podría no existir.
                // Para estar seguros, verificamos el tipo de pregunta.
                $question = $this->questionModel->getByIdWithType($id);
                $typeModel = $this->model('QuestionType');
                if (!$typeModel->requiresOptions($question['type_id'])) {
                    $this->questionOptionModel->deleteByQuestionId($id);
                }
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Pregunta actualizada exitosamente']);
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Error al actualizar pregunta: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno al actualizar la pregunta.'], 500);
        }
    }

    /**
     * Sincroniza las opciones y la lógica condicional de una pregunta.
     */
    private function syncOptionsAndConditionals($questionId, $submittedOptions, $db) {
        $existingOptions = $this->questionOptionModel->getByQuestionId($questionId, $db);
        $existingOptionIds = array_column($existingOptions, 'id');
        $submittedOptionIds = [];

        foreach ($submittedOptions as $optionData) {
            $optionId = $optionData['id'];

            // Opción nueva
            if (empty($optionId) || strpos($optionId, 'new_') === 0) {
                $newOptionId = $this->questionOptionModel->create([
                    'question_id' => $questionId,
                    'option_text' => $optionData['option_text'],
                    'order_number' => $optionData['order_number']
                ], $db);
                $submittedOptionIds[] = $newOptionId;
                $this->updateConditional($newOptionId, $optionData['child_question_id'], $db);
            } 
            // Opción existente
            else {
                $submittedOptionIds[] = $optionId;
                // Ahora la llamada a update funcionará correctamente
                $this->questionOptionModel->update($optionId, [
                    'option_text' => $optionData['option_text'],
                    'order_number' => $optionData['order_number']
                ], $db);
                $this->updateConditional($optionId, $optionData['child_question_id'], $db);
            }
        }

        // Eliminar opciones que ya no existen
        $optionsToDelete = array_diff($existingOptionIds, $submittedOptionIds);
        if (!empty($optionsToDelete)) {
            $this->questionOptionModel->deleteByQuestionIdAndOptionIds($questionId, $optionsToDelete, $db);
        }
    }

    /**
     * Actualiza la lógica condicional para una opción.
     */
    private function updateConditional($optionId, $childQuestionId, $db) {
        $this->questionModel->query("DELETE FROM question_children WHERE parent_option_id = ?", [$optionId], $db);

        if (!empty($childQuestionId)) {
            $this->questionModel->query(
                "INSERT INTO question_children (parent_option_id, child_question_id) VALUES (?, ?)",
                [$optionId, $childQuestionId],
                $db
            );
        }
    }
    
    /**
     * Eliminar pregunta (AJAX)
     */
    public function delete() {
        if (!$this->validateRequest()) return;
        
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID no válido'], 400);
            return;
        }
        if (!$this->csrf->validate($input['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            return;
        }
        
        if (!$this->questionModel->canDelete($id)) {
            $this->json(['success' => false, 'message' => 'No se puede eliminar la pregunta porque tiene respuestas registradas'], 409);
            return;
        }
        
        if ($this->questionModel->delete($id)) {
            $this->json(['success' => true, 'message' => 'Pregunta eliminada exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar la pregunta'], 500);
        }
    }
    
    /**
     * Reordenar preguntas (AJAX)
     */
    public function reorder() {
        if (!$this->validateRequest()) return;
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->csrf->validate($input['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            return;
        }
        
        if (empty($input['form_id']) || !isset($input['orders'])) {
            $this->json(['success' => false, 'message' => 'Datos incompletos'], 422);
            return;
        }
        
        if ($this->questionModel->reorder($input['form_id'], $input['orders'])) {
            $this->json(['success' => true, 'message' => 'Orden actualizado']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al reordenar'], 500);
        }
    }
    
    /**
     * Obtener pregunta por ID (AJAX)
     */
    public function get() {
        if (!$this->isAjax() || !$this->auth->check()) {
            $this->json(['success' => false, 'message' => 'Acceso no permitido'], 403);
            return;
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID no válido'], 400);
            return;
        }
        
        // Pasar la conexión a la base de datos
        $question = $this->questionModel->getWithOptions($id, Model::getDbConnection());
        
        if (!$question) {
            $this->json(['success' => false, 'message' => 'Pregunta no encontrada'], 404);
            return;
        }

        $typeModel = $this->model('QuestionType');
        $type = $typeModel->getById($question['type_id']);
        $question['type_name'] = $type['name'];
        $question['type_label'] = $type['description'];
        
        $this->json(['success' => true, 'question' => $question]);
    }
}
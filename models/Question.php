<?php
/**
 * Modelo: Question
 * Gestiona las preguntas de los formularios
 */

class Question extends Model {
    protected $table = 'questions';
    
    /**
     * Obtener pregunta por ID con tipo
     */
    public function getByIdWithType($id) {
        $sql = "SELECT q.*, 
                       qt.name as type_name, 
                       qt.description as type_label
                FROM {$this->table} q
                INNER JOIN question_types qt ON q.type_id = qt.id
                WHERE q.id = ?";
        
        $result = $this->query($sql, [$id]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Obtener todas las preguntas de un formulario
     */
    public function getByFormId($formId) {
        $sql = "SELECT q.*, 
                       qt.name as type_name, 
                       qt.description as type_label
                FROM {$this->table} q
                INNER JOIN question_types qt ON q.type_id = qt.id
                WHERE q.form_id = ?
                ORDER BY q.order_number ASC";
        
        return $this->query($sql, [$formId]);
    }
    
    /**
     * Crear nueva pregunta
     */
    public function create($data) {
        // Obtener el siguiente order_number
        $sql = "SELECT COALESCE(MAX(order_number), 0) + 1 as next_order 
                FROM {$this->table} 
                WHERE form_id = ?";
        
        $result = $this->query($sql, [$data['form_id']]);
        $orderNumber = $data['order_number'] ?? $result[0]['next_order'];
        
        $sql = "INSERT INTO {$this->table} 
                (form_id, question_text, type_id, required, order_number, 
                 placeholder, help_text, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $this->query($sql, [
            $data['form_id'],
            $data['question_text'],
            $data['type_id'],
            $data['required'] ?? 0,
            $orderNumber,
            $data['placeholder'] ?? null,
            $data['help_text'] ?? null,
            $data['created_by']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar pregunta
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET question_text = ?,
                    type_id = ?,
                    required = ?,
                    placeholder = ?,
                    help_text = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        return $this->query($sql, [
            $data['question_text'],
            $data['type_id'],
            $data['required'] ?? 0,
            $data['placeholder'] ?? null,
            $data['help_text'] ?? null,
            $id
        ]);
    }
    
    /**
     * Eliminar pregunta
     */
    public function delete($id) {
        // Verificar que no tenga respuestas
        if (!$this->canDelete($id)) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->query($sql, [$id]);
    }
    
    /**
     * Verificar si la pregunta puede ser eliminada
     */
    public function canDelete($id) {
        $sql = "SELECT COUNT(*) as count 
                FROM answers 
                WHERE question_id = ?";
        
        $result = $this->query($sql, [$id]);
        return $result[0]['count'] == 0;
    }
    
    /**
     * Reordenar preguntas de un formulario
     */
    public function reorder($formId, $questionOrders) {
        // $questionOrders es un array: [questionId => orderNumber]
        foreach ($questionOrders as $questionId => $orderNumber) {
            $sql = "UPDATE {$this->table} 
                    SET order_number = ?,
                        updated_at = NOW()
                    WHERE id = ? AND form_id = ?";
            
            $this->query($sql, [$orderNumber, $questionId, $formId]);
        }
        
        return true;
    }
    
    /**
     * Obtener opciones de una pregunta
     */
    public function getOptions($questionId) {
        $sql = "SELECT * FROM question_options 
                WHERE question_id = ? 
                ORDER BY order_number ASC";
        
        return $this->query($sql, [$questionId]);
    }
    
    /**
     * Obtener pregunta con opciones
     */
    public function getWithOptions($id) {
        $question = $this->getByIdWithType($id);
        
        if ($question) {
            $question['options'] = $this->getOptions($id);
        }
        
        return $question;
    }
    
    /**
     * Obtener todas las preguntas de un formulario con sus opciones
     */
    public function getFormQuestionsWithOptions($formId) {
        $questions = $this->getByFormId($formId);
        
        foreach ($questions as &$question) {
            $question['options'] = $this->getOptions($question['id']);
        }
        
        return $questions;
    }
    
    /**
     * Obtener preguntas hijas de una opción
     */
    public function getChildrenByOption($optionId) {
        $sql = "SELECT q.*, 
                       qt.name as type_name,
                       qt.description as type_label
                FROM {$this->table} q
                INNER JOIN question_types qt ON q.type_id = qt.id
                INNER JOIN question_children qc ON q.id = qc.child_question_id
                WHERE qc.parent_option_id = ?
                ORDER BY q.order_number ASC";
        
        return $this->query($sql, [$optionId]);
    }
    
    /**
     * Mover pregunta arriba en el orden
     */
    public function moveUp($id) {
        $question = $this->getById($id);
        if (!$question || $question['order_number'] <= 1) {
            return false;
        }
        
        $formId = $question['form_id'];
        $currentOrder = $question['order_number'];
        $newOrder = $currentOrder - 1;
        
        // Intercambiar con la pregunta anterior
        $sql = "UPDATE {$this->table} 
                SET order_number = CASE 
                    WHEN order_number = ? THEN ?
                    WHEN order_number = ? THEN ?
                END,
                updated_at = NOW()
                WHERE form_id = ? AND order_number IN (?, ?)";
        
        return $this->query($sql, [
            $currentOrder, $newOrder,
            $newOrder, $currentOrder,
            $formId, $currentOrder, $newOrder
        ]);
    }
    
    /**
     * Mover pregunta abajo en el orden
     */
    public function moveDown($id) {
        $question = $this->getById($id);
        if (!$question) {
            return false;
        }
        
        $formId = $question['form_id'];
        $currentOrder = $question['order_number'];
        
        // Verificar que haya una pregunta siguiente
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE form_id = ? AND order_number > ?";
        $result = $this->query($sql, [$formId, $currentOrder]);
        
        if ($result[0]['count'] == 0) {
            return false;
        }
        
        $newOrder = $currentOrder + 1;
        
        // Intercambiar con la pregunta siguiente
        $sql = "UPDATE {$this->table} 
                SET order_number = CASE 
                    WHEN order_number = ? THEN ?
                    WHEN order_number = ? THEN ?
                END,
                updated_at = NOW()
                WHERE form_id = ? AND order_number IN (?, ?)";
        
        return $this->query($sql, [
            $currentOrder, $newOrder,
            $newOrder, $currentOrder,
            $formId, $currentOrder, $newOrder
        ]);
    }
    
    /**
     * Duplicar pregunta
     */
    public function duplicate($id, $userId) {
        $question = $this->getWithOptions($id);
        if (!$question) {
            return false;
        }
        
        // Crear nueva pregunta
        $newQuestionId = $this->create([
            'form_id' => $question['form_id'],
            'question_text' => $question['question_text'] . ' (Copia)',
            'type_id' => $question['type_id'],
            'required' => $question['required'],
            'placeholder' => $question['placeholder'],
            'help_text' => $question['help_text'],
            'created_by' => $userId
        ]);
        
        // Copiar opciones si las tiene
        if (!empty($question['options'])) {
            $optionModel = new QuestionOption();
            foreach ($question['options'] as $option) {
                $optionModel->create([
                    'question_id' => $newQuestionId,
                    'option_text' => $option['option_text'],
                    'value' => $option['value'],
                    'order_number' => $option['order_number']
                ]);
            }
        }
        
        return $newQuestionId;
    }
}
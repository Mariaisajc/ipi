<?php
/**
 * Modelo: QuestionOption
 * Gestiona las opciones de las preguntas (radio, checkbox, select)
 */

class QuestionOption extends Model {
    protected $table = 'question_options';
    
    /**
     * Obtener todas las opciones de una pregunta
     */
    public function getByQuestionId($questionId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE question_id = ? 
                ORDER BY order_number ASC";
        
        return $this->query($sql, [$questionId]);
    }
    
    /**
     * Crear nueva opción
     */
    public function create($data) {
        // Obtener el siguiente order_number
        $sql = "SELECT COALESCE(MAX(order_number), 0) + 1 as next_order 
                FROM {$this->table} 
                WHERE question_id = ?";
        
        $result = $this->query($sql, [$data['question_id']]);
        $orderNumber = $data['order_number'] ?? $result[0]['next_order'];
        
        $sql = "INSERT INTO {$this->table} 
                (question_id, option_text, value, order_number, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->query($sql, [
            $data['question_id'],
            $data['option_text'],
            $data['value'] ?? $data['option_text'],
            $orderNumber
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar opción
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET option_text = ?,
                    value = ?
                WHERE id = ?";
        
        return $this->query($sql, [
            $data['option_text'],
            $data['value'] ?? $data['option_text'],
            $id
        ]);
    }
    
    /**
     * Eliminar opción
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->query($sql, [$id]);
    }
    
    /**
     * Eliminar todas las opciones de una pregunta
     */
    public function deleteByQuestionId($questionId) {
        $sql = "DELETE FROM {$this->table} WHERE question_id = ?";
        return $this->query($sql, [$questionId]);
    }
    
    /**
     * Reordenar opciones
     */
    public function reorder($questionId, $optionOrders) {
        // $optionOrders es un array: [optionId => orderNumber]
        foreach ($optionOrders as $optionId => $orderNumber) {
            $sql = "UPDATE {$this->table} 
                    SET order_number = ?
                    WHERE id = ? AND question_id = ?";
            
            $this->query($sql, [$orderNumber, $optionId, $questionId]);
        }
        
        return true;
    }
    
    /**
     * Obtener preguntas hijas de esta opción (lógica condicional)
     */
    public function getChildren($optionId) {
        $sql = "SELECT q.*, qt.name as type_name
                FROM questions q
                INNER JOIN question_types qt ON q.type_id = qt.id
                INNER JOIN question_children qc ON q.id = qc.child_question_id
                WHERE qc.parent_option_id = ?
                ORDER BY q.order_number ASC";
        
        return $this->query($sql, [$optionId]);
    }
    
    /**
     * Agregar pregunta hija (lógica condicional)
     */
    public function addChild($optionId, $childQuestionId) {
        $sql = "INSERT INTO question_children (parent_option_id, child_question_id) 
                VALUES (?, ?)";
        
        return $this->query($sql, [$optionId, $childQuestionId]);
    }
    
    /**
     * Eliminar pregunta hija
     */
    public function removeChild($optionId, $childQuestionId) {
        $sql = "DELETE FROM question_children 
                WHERE parent_option_id = ? AND child_question_id = ?";
        
        return $this->query($sql, [$optionId, $childQuestionId]);
    }
    
    /**
     * Verificar si una opción tiene preguntas hijas
     */
    public function hasChildren($optionId) {
        $sql = "SELECT COUNT(*) as count 
                FROM question_children 
                WHERE parent_option_id = ?";
        
        $result = $this->query($sql, [$optionId]);
        return $result[0]['count'] > 0;
    }
    
    /**
     * Crear múltiples opciones a la vez
     */
    public function createMultiple($questionId, $options) {
        $createdIds = [];
        
        foreach ($options as $index => $optionData) {
            $id = $this->create([
                'question_id' => $questionId,
                'option_text' => $optionData['text'],
                'value' => $optionData['value'] ?? $optionData['text'],
                'order_number' => $index + 1
            ]);
            
            $createdIds[] = $id;
        }
        
        return $createdIds;
    }
}
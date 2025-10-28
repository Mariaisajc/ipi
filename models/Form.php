<?php
/**
 * Modelo: Form
 * Gestiona los formularios del sistema
 */

class Form extends Model {
    protected $table = 'forms';
    
    /**
     * Obtener todos los formularios con información del creador
     */
    public function getAllWithCreator() {
        $sql = "SELECT f.*, 
                       u.login as creator_login,
                       u.name as creator_name,
                       (SELECT COUNT(*) FROM questions WHERE form_id = f.id) as question_count,
                       (SELECT COUNT(*) FROM user_forms WHERE form_id = f.id) as assignment_count,
                       (SELECT COUNT(*) FROM responses WHERE form_id = f.id AND status = 'completed') as response_count
                FROM {$this->table} f
                LEFT JOIN users u ON f.created_by = u.id
                ORDER BY f.created_at DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Obtener formulario por ID con información adicional
     */
    public function getByIdWithDetails($id) {
        $sql = "SELECT f.*, 
                       u.login as creator_login,
                       u.name as creator_name,
                       (SELECT COUNT(*) FROM questions WHERE form_id = f.id) as question_count,
                       (SELECT COUNT(*) FROM user_forms WHERE form_id = f.id) as assignment_count,
                       (SELECT COUNT(*) FROM responses WHERE form_id = f.id AND status = 'completed') as response_count
                FROM {$this->table} f
                LEFT JOIN users u ON f.created_by = u.id
                WHERE f.id = ?";
        
        $result = $this->query($sql, [$id]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Obtener formularios por estado
     */
    public function getByStatus($status) {
        $sql = "SELECT f.*, 
                       u.login as creator_login,
                       (SELECT COUNT(*) FROM questions WHERE form_id = f.id) as question_count
                FROM {$this->table} f
                LEFT JOIN users u ON f.created_by = u.id
                WHERE f.status = ?
                ORDER BY f.created_at DESC";
        
        return $this->query($sql, [$status]);
    }
    
    /**
     * Crear nuevo formulario
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (title, description, status, created_by, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->query($sql, [
            $data['title'],
            $data['description'] ?? null,
            $data['status'] ?? 'draft',
            $data['created_by']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar formulario
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET title = ?, 
                    description = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        return $this->query($sql, [
            $data['title'],
            $data['description'] ?? null,
            $id
        ]);
    }
    
    /**
     * Cambiar estado del formulario
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} 
                SET status = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        return $this->query($sql, [$status, $id]);
    }
    
    /**
     * Publicar formulario (cambiar a estado active)
     */
    public function publish($id) {
        return $this->updateStatus($id, 'active');
    }
    
    /**
     * Cerrar formulario (no acepta más respuestas)
     */
    public function close($id) {
        return $this->updateStatus($id, 'closed');
    }
    
    /**
     * Verificar si el formulario puede ser eliminado
     */
    public function canDelete($id) {
        // No se puede eliminar si tiene respuestas completadas
        $sql = "SELECT COUNT(*) as count 
                FROM responses 
                WHERE form_id = ? AND status = 'completed'";
        
        $result = $this->query($sql, [$id]);
        return $result[0]['count'] == 0;
    }
    
    /**
     * Eliminar formulario
     */
    public function delete($id) {
        if (!$this->canDelete($id)) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->query($sql, [$id]);
    }
    
    /**
     * Obtener todas las preguntas del formulario
     */
    public function getQuestions($formId) {
        $sql = "SELECT q.*, qt.name as type_name, qt.description as type_description
                FROM questions q
                INNER JOIN question_types qt ON q.type_id = qt.id
                WHERE q.form_id = ?
                ORDER BY q.order_number ASC";
        
        return $this->query($sql, [$formId]);
    }
    
    /**
     * Duplicar formulario
     */
    public function duplicate($id, $userId) {
        // Obtener formulario original
        $original = $this->getById($id);
        if (!$original) {
            return false;
        }
        
        // Crear nuevo formulario
        $newFormId = $this->create([
            'title' => $original['title'] . ' (Copia)',
            'description' => $original['description'],
            'status' => 'draft',
            'created_by' => $userId
        ]);
        
        // Copiar preguntas
        $questions = $this->getQuestions($id);
        $questionModel = new Question();
        
        foreach ($questions as $question) {
            $questionModel->create([
                'form_id' => $newFormId,
                'question_text' => $question['question_text'],
                'type_id' => $question['type_id'],
                'required' => $question['required'],
                'order_number' => $question['order_number'],
                'placeholder' => $question['placeholder'],
                'help_text' => $question['help_text'],
                'created_by' => $userId
            ]);
        }
        
        return $newFormId;
    }
    
    /**
     * Verificar si el formulario tiene preguntas
     */
    public function hasQuestions($id) {
        $sql = "SELECT COUNT(*) as count FROM questions WHERE form_id = ?";
        $result = $this->query($sql, [$id]);
        return $result[0]['count'] > 0;
    }
    
    /**
     * Obtener estadísticas del formulario
     */
    public function getStatistics($id) {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM questions WHERE form_id = ?) as total_questions,
                    (SELECT COUNT(*) FROM user_forms WHERE form_id = ?) as total_assignments,
                    (SELECT COUNT(*) FROM responses WHERE form_id = ? AND status = 'started') as started_responses,
                    (SELECT COUNT(*) FROM responses WHERE form_id = ? AND status = 'in_progress') as in_progress_responses,
                    (SELECT COUNT(*) FROM responses WHERE form_id = ? AND status = 'completed') as completed_responses";
        
        $result = $this->query($sql, [$id, $id, $id, $id, $id]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Buscar formularios por título
     */
    public function search($searchTerm) {
        $sql = "SELECT f.*, 
                       u.login as creator_login,
                       (SELECT COUNT(*) FROM questions WHERE form_id = f.id) as question_count
                FROM {$this->table} f
                LEFT JOIN users u ON f.created_by = u.id
                WHERE f.title LIKE ?
                ORDER BY f.created_at DESC";
        
        return $this->query($sql, ['%' . $searchTerm . '%']);
    }
}
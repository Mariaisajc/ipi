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
     * Actualizar formulario (MODIFICADO para ser más flexible)
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $params[] = $data['title'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $params[] = $data['description'];
        }

        if (empty($fields)) {
            return true; // No hay nada que actualizar
        }

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $params[] = $id;
        
        return $this->query($sql, $params);
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
        // CORRECCIÓN: El orden correcto es ($id, $status)
        return $this->updateStatus($id, 'active');
    }
    
    /**
     * Cerrar formulario (no acepta más respuestas)
     */
    public function close($id) {
        // CORRECCIÓN: El orden correcto es ($id, $status)
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
     * Duplicar formulario (MODIFICADO Y CORREGIDO)
     */
    public function duplicate($id, $userId) {
        // Obtener formulario original
        $original = $this->getByIdWithDetails($id);
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
        
        if (!$newFormId) {
            return false;
        }

        // Copiar preguntas y sus opciones
        $questionModel = new Question();
        $optionModel = new QuestionOption();
        $questions = $this->getQuestions($id);
        
        foreach ($questions as $question) {
            $newQuestionId = $questionModel->create([
                'form_id' => $newFormId,
                'question_text' => $question['question_text'],
                'type_id' => $question['type_id'],
                'required' => $question['required'],
                'order_number' => $question['order_number'],
                'placeholder' => $question['placeholder'],
                'help_text' => $question['help_text'],
                'created_by' => $userId
            ]);

            // Si la pregunta tiene opciones, copiarlas también
            $options = $optionModel->getByQuestionId($question['id']);
            if (!empty($options)) {
                foreach ($options as $option) {
                    $optionModel->create([
                        'question_id' => $newQuestionId,
                        'option_text' => $option['option_text'],
                        'value' => $option['value'],
                        'order_number' => $option['order_number']
                    ]);
                }
            }
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
                       u.name as creator_name,
                       (SELECT COUNT(*) FROM questions WHERE form_id = f.id) as question_count,
                       (SELECT COUNT(*) FROM user_forms WHERE form_id = f.id) as assignment_count,
                       (SELECT COUNT(*) FROM responses WHERE form_id = f.id AND status = 'completed') as response_count
                FROM {$this->table} f
                LEFT JOIN users u ON f.created_by = u.id
                WHERE f.title LIKE ?
                ORDER BY f.created_at DESC";
        
        return $this->query($sql, ['%' . $searchTerm . '%']);
    }
    
    /**
     * NUEVO: Filtrar formularios por múltiples criterios (búsqueda y/o estado)
     */
    public function filter($filters) {
        $sql = "SELECT f.*, 
                       u.login as creator_login,
                       u.name as creator_name,
                       (SELECT COUNT(*) FROM questions WHERE form_id = f.id) as question_count,
                       (SELECT COUNT(*) FROM user_forms WHERE form_id = f.id) as assignment_count,
                       (SELECT COUNT(*) FROM responses WHERE form_id = f.id AND status = 'completed') as response_count
                FROM {$this->table} f
                LEFT JOIN users u ON f.created_by = u.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtro por término de búsqueda
        if (!empty($filters['search'])) {
            $sql .= " AND f.title LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }
        
        // Filtro por estado
        if (!empty($filters['status'])) {
            $sql .= " AND f.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY f.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Sincroniza los usuarios asignados a un formulario.
     * Asigna los nuevos y desasigna los que ya no están seleccionados.
     */
    public function syncAssignedUsers($formId, $selectedUserIds, $adminId) {
        $db = Model::getDbConnection();
        
        // Obtener los usuarios actualmente asignados
        $sql_current = "SELECT user_id FROM user_forms WHERE form_id = ?";
        $currentAssignments = $this->query($sql_current, [$formId], $db);
        $currentUserIds = array_column($currentAssignments, 'user_id');
        
        // 1. Usuarios a asignar (los que están en 'selected' pero no en 'current')
        $usersToAssign = array_diff($selectedUserIds, $currentUserIds);
        if (!empty($usersToAssign)) {
            $sql_assign = "INSERT INTO user_forms (form_id, user_id, assigned_by) VALUES ";
            $params = [];
            $rows = [];
            foreach ($usersToAssign as $userId) {
                $rows[] = "(?, ?, ?)";
                array_push($params, $formId, $userId, $adminId);
            }
            $sql_assign .= implode(', ', $rows);
            $this->query($sql_assign, $params, $db);
        }
        
        // 2. Usuarios a desasignar (los que están en 'current' pero no en 'selected')
        $usersToUnassign = array_diff($currentUserIds, $selectedUserIds);
        if (!empty($usersToUnassign)) {
            $placeholders = implode(',', array_fill(0, count($usersToUnassign), '?'));
            $sql_unassign = "DELETE FROM user_forms WHERE form_id = ? AND user_id IN ($placeholders)";
            $params = array_merge([$formId], $usersToUnassign);
            $this->query($sql_unassign, $params, $db);
        }
        
        return true;
    }
}
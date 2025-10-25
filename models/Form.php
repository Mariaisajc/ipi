<?php
/**
 * IPI - Innovation Performance Index
 * Modelo: Form
 * 
 * Gestión de formularios
 */

class Form extends Model {
    protected $table = 'forms';
    protected $primaryKey = 'id';
    
    /**
     * Obtener todos los formularios con información adicional
     * 
     * @param array $filters
     * @return array
     */
    public function getAllWithDetails($filters = []) {
        $sql = "SELECT 
                    f.*,
                    u.name as creator_name,
                    (SELECT COUNT(*) FROM questions WHERE form_id = f.id) as total_questions,
                    (SELECT COUNT(*) FROM responses WHERE form_id = f.id) as total_responses
                FROM {$this->table} f
                LEFT JOIN users u ON f.created_by = u.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtrar por estado
        if (!empty($filters['status'])) {
            $sql .= " AND f.status = :status";
            $params['status'] = $filters['status'];
        }
        
        // Búsqueda por texto
        if (!empty($filters['search'])) {
            $sql .= " AND (f.title LIKE :search OR f.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY f.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Obtener formulario con preguntas
     * 
     * @param int $id
     * @return array|false
     */
    public function getWithQuestions($id) {
        $form = $this->find($id);
        
        if (!$form) {
            return false;
        }
        
        // Obtener preguntas del formulario
        $sql = "SELECT q.*, qt.name as type_name
                FROM questions q
                INNER JOIN question_types qt ON q.type_id = qt.id
                WHERE q.form_id = :form_id
                ORDER BY q.order_number ASC";
        
        $form['questions'] = $this->query($sql, ['form_id' => $id]);
        
        return $form;
    }
    
    /**
     * Cambiar estado del formulario
     * 
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function changeStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }
}
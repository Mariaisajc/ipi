<?php
/**
 * IPI - Innovation Performance Index
 * Modelo: Response
 * 
 * Gestión de respuestas de formularios
 */

class Response extends Model {
    protected $table = 'responses';
    protected $primaryKey = 'id';
    protected $timestamps = true;
    
    /**
     * Crear nueva respuesta con UUID
     * 
     * @param array $data
     * @return string UUID de la respuesta
     */
    public function create($data) {
        // Generar UUID si no existe
        if (!isset($data['id'])) {
            require_once SERVICES_PATH . '/UUIDService.php';
            $data['id'] = UUIDService::generate();
        }
        
        // Establecer valores por defecto
        $data['status'] = $data['status'] ?? 'started';
        $data['started_at'] = $data['started_at'] ?? date('Y-m-d H:i:s');
        
        $fields = array_keys($data);
        $values = ':' . implode(', :', $fields);
        $fields = implode(', ', $fields);
        
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$values})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return $data['id'];
    }
    
    /**
     * Obtener respuestas de un formulario con información del usuario
     * 
     * @param int $formId
     * @param array $filters
     * @return array
     */
    public function getByForm($formId, $filters = []) {
        $sql = "SELECT 
                    r.*,
                    u.name as user_name,
                    u.login as user_login,
                    b.name as business_name,
                    ba.name as area_name
                FROM {$this->table} r
                INNER JOIN users u ON r.user_id = u.id
                LEFT JOIN businesses b ON u.business_id = b.id
                LEFT JOIN business_areas ba ON r.business_area_id = ba.id
                WHERE r.form_id = :form_id";
        
        $params = ['form_id' => $formId];
        
        // Filtrar por estado
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params['status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY r.started_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Obtener respuesta con todas las respuestas individuales
     * 
     * @param string $responseId
     * @return array|false
     */
    public function getWithAnswers($responseId) {
        $response = $this->find($responseId);
        
        if (!$response) {
            return false;
        }
        
        // Obtener información del usuario y formulario
        $sql = "SELECT 
                    r.*,
                    u.name as user_name,
                    u.email as user_email,
                    f.title as form_title,
                    b.name as business_name
                FROM {$this->table} r
                INNER JOIN users u ON r.user_id = u.id
                INNER JOIN forms f ON r.form_id = f.id
                LEFT JOIN businesses b ON u.business_id = b.id
                WHERE r.id = :id";
        
        $response = $this->query($sql, ['id' => $responseId])[0] ?? false;
        
        if (!$response) {
            return false;
        }
        
        // Obtener respuestas individuales
        $sql = "SELECT 
                    a.*,
                    q.question_text,
                    qt.name as question_type
                FROM answers a
                INNER JOIN questions q ON a.question_id = q.id
                INNER JOIN question_types qt ON q.type_id = qt.id
                WHERE a.response_id = :response_id
                ORDER BY q.order_number ASC";
        
        $response['answers'] = $this->query($sql, ['response_id' => $responseId]);
        
        return $response;
    }
    
    /**
     * Contar respuestas por estado
     * 
     * @param int $formId
     * @return array
     */
    public function countByStatus($formId) {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM {$this->table}
                WHERE form_id = :form_id
                GROUP BY status";
        
        $result = $this->query($sql, ['form_id' => $formId]);
        
        $counts = [
            'started' => 0,
            'in_progress' => 0,
            'completed' => 0
        ];
        
        foreach ($result as $row) {
            $counts[$row['status']] = (int) $row['count'];
        }
        
        return $counts;
    }
}
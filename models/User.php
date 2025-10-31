<?php
/**
 * User Model
 * Gestión de usuarios del sistema
 */

class User extends Model {
    
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    /**
     * Obtener todos los usuarios con paginación
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT u.*, 
                b.name as business_name,
                creator.name as created_by_name,
                (SELECT COUNT(*) FROM user_forms WHERE user_id = u.id) as assigned_forms_count
                FROM {$this->table} u
                LEFT JOIN businesses b ON u.business_id = b.id
                LEFT JOIN users creator ON u.created_by = creator.id
                ORDER BY u.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->query($sql);
    }
    
    /**
     * Obtener usuario por ID
     */
    public function getById($id) {
        $sql = "SELECT u.*, b.name as business_name 
                FROM {$this->table} u
                LEFT JOIN businesses b ON u.business_id = b.id
                WHERE u.id = ?";
        
        $result = $this->query($sql, [$id]);
        return $result ? $result[0] : null;
    }

    /**
     * Activa/desactiva usuarios 'encuestado' según sus fechas de vigencia.
     * Este método centraliza la lógica del cron job para uso en tiempo real.
     */
    public function manageUserStatusByDates() {
        $today = date('Y-m-d');

        // Activar usuarios que están dentro de su período de vigencia y están inactivos
        $sqlActivate = "UPDATE {$this->table} 
                        SET status = 'active' 
                        WHERE role = 'encuestado' 
                          AND status = 'inactive'
                          AND ? BETWEEN start_date AND end_date";
        $this->query($sqlActivate, [$today]);

        // Desactivar usuarios que están fuera de su período de vigencia y están activos
        $sqlDeactivate = "UPDATE {$this->table} 
                          SET status = 'inactive' 
                          WHERE role = 'encuestado' 
                            AND status = 'active'
                            AND (? NOT BETWEEN start_date AND end_date)";
        $this->query($sqlDeactivate, [$today]);
    }

    /**
     * Obtener todos los usuarios con filtros y paginación
     */
    public function getAllPaginated($filters = [], $page = 1, $perPage = 15) {
        $sql = "SELECT u.*, 
                b.name as business_name,
                creator.name as created_by_name
                FROM {$this->table} u
                LEFT JOIN businesses b ON u.business_id = b.id
                LEFT JOIN users creator ON u.created_by = creator.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtros de búsqueda
        if (!empty($filters['search'])) {
            $sql .= " AND (u.login LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Filtro por rol
        if (!empty($filters['role'])) {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }
        
        // Filtro por estado
        if (!empty($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }
        
        // Filtro por empresa
        if (!empty($filters['business_id'])) {
            $sql .= " AND u.business_id = ?";
            $params[] = $filters['business_id'];
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        // Paginación
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Obtener usuario por login
     */
    public function getByLogin($login) {
        $sql = "SELECT * FROM {$this->table} WHERE login = ?";
        $result = $this->query($sql, [$login]);
        return $result[0] ?? null;
    }
    
    /**
     * Obtener usuario por email
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $result = $this->query($sql, [$email]);
        return $result[0] ?? null;
    }
    
    /**
     * Buscar usuario por login o email
     * Usado para verificar existencia en el login
     * 
     * @param string $login Login o email del usuario
     * @return array|null Usuario encontrado o null
     */
    public function findByLogin($login) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (login = ? OR email = ?)
                LIMIT 1";
        
        $result = $this->query($sql, [$login, $login]);
        
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Crear nuevo usuario
     */
    public function create($data) {
        // Convertir email vacío a NULL para evitar duplicados
        if (isset($data['email']) && empty($data['email'])) {
            $data['email'] = null;
        }
        
        // Usar el método create del padre que maneja INSERT automáticamente
        return parent::create($data);
    }
    
    /**
     * Actualizar usuario
     */
    public function update($id, $data) {
        // Convertir email vacío a NULL para evitar duplicados
        if (isset($data['email']) && empty($data['email'])) {
            $data['email'] = null;
        }
        
        // Si no hay contraseña nueva, eliminarla del array
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        
        // Usar el método update del padre
        return parent::update($id, $data);
    }
    
    /**
     * Eliminar usuario
     */
    public function delete($id) {
        // CORREGIDO: Implementar la consulta SQL de borrado directamente
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Actualizar último login
     */
    public function updateLastLogin($id) {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Buscar usuarios
     */
    public function search($term) {
        $sql = "SELECT u.*, 
                b.name as business_name,
                creator.name as created_by_name
                FROM {$this->table} u
                LEFT JOIN businesses b ON u.business_id = b.id
                LEFT JOIN users creator ON u.created_by = creator.id
                WHERE u.login LIKE ? OR u.name LIKE ? OR u.email LIKE ?
                ORDER BY u.name ASC";
        
        $searchTerm = "%{$term}%";
        return $this->query($sql, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    /**
     * Filtrar usuarios con múltiples criterios
     */
    public function filter($filters) {
        $sql = "SELECT u.*, 
                b.name as business_name,
                creator.name as created_by_name,
                (SELECT COUNT(*) FROM user_forms WHERE user_id = u.id) as assigned_forms_count
                FROM {$this->table} u
                LEFT JOIN businesses b ON u.business_id = b.id
                LEFT JOIN users creator ON u.created_by = creator.id
                WHERE 1=1";
        
        $params = [];
        
        // Búsqueda por texto
        if (!empty($filters['search'])) {
            $sql .= " AND (u.login LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Filtro por rol
        if (!empty($filters['role'])) {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }
        
        // Filtro por estado
        if (!empty($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }
        
        // Filtro por empresa
        if (!empty($filters['business_id'])) {
            $sql .= " AND u.business_id = ?";
            $params[] = $filters['business_id'];
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Contar total de usuarios
     */
    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->query($sql);
        return $result[0]['total'] ?? 0;
    }
    
    /**
     * Verificar si el login ya existe
     */
    public function loginExists($login, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table} WHERE login = ?";
        $params = [$login];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->query($sql, $params);
        return !empty($result);
    }
    
    /**
     * Verificar si el email ya existe
     */
    public function emailExists($email, $excludeId = null) {
        if (empty($email)) return false;
        
        $sql = "SELECT id FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->query($sql, $params);
        return !empty($result);
    }
    
    /**
     * Verificar si un usuario puede ser eliminado
     * No se puede eliminar si:
     * 1. Es admin (no se eliminan entre ellos)
     * 2. Tiene respuestas asociadas
     * 3. Tiene formularios asignados
     */
    public function canDelete($id) {
        // Regla #5: Proteger al super admin
        if ($id == 1) {
            return ['can_delete' => false, 'reason' => 'El administrador principal (ID 1) no puede ser eliminado.'];
        }

        $user = $this->getById($id);
        
        if (!$user) {
            return ['can_delete' => false, 'reason' => 'Usuario no encontrado'];
        }
        
        // Regla: Los admin no se pueden eliminar
        if ($user['role'] === 'admin') {
            return ['can_delete' => false, 'reason' => 'Los administradores no pueden ser eliminados por seguridad.'];
        }
        
        // Regla #4: Verificar si tiene respuestas asociadas
        // CORRECCIÓN: La consulta ahora une 'answers' con 'responses' para encontrar el 'user_id'
        $sqlResponses = "SELECT COUNT(a.id) as total 
                         FROM answers a
                         INNER JOIN responses r ON a.response_id = r.id
                         WHERE r.user_id = ?";
        $responses = $this->query($sqlResponses, [$id]);
        if ($responses[0]['total'] > 0) {
            return ['can_delete' => false, 'reason' => 'El usuario no se puede eliminar porque tiene respuestas asociadas.'];
        }
        
        // NUEVO: Regla #5: Verificar si tiene formularios asignados
        $sqlAssigned = "SELECT COUNT(*) as total FROM user_forms WHERE user_id = ?";
        $assigned = $this->query($sqlAssigned, [$id]);
        if ($assigned[0]['total'] > 0) {
            return ['can_delete' => false, 'reason' => 'El usuario no se puede eliminar porque tiene formularios asignados.'];
        }

        // Regla #6: Verificar si tiene formularios creados
        $sqlForms = "SELECT COUNT(*) as total FROM forms WHERE created_by = ?";
        $forms = $this->query($sqlForms, [$id]);
        if ($forms[0]['total'] > 0) {
            return ['can_delete' => false, 'reason' => 'El usuario no se puede eliminar porque ha creado formularios.'];
        }
        
        return ['can_delete' => true, 'reason' => ''];
    }
    
    /**
     * Contar usuarios por rol
     * 
     * @return array ['admin' => count, 'encuestado' => count]
     */
    public function countByRole() {
        $sql = "SELECT role, COUNT(*) as count 
                FROM {$this->table} 
                WHERE status = 'active'
                GROUP BY role";
        
        $results = $this->query($sql);
        
        $counts = [
            'admin' => 0,
            'encuestado' => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['role']] = (int)$row['count'];
        }
        
        return $counts;
    }
    
    /**
     * Buscar usuario activo por login o email
     * Usado para autenticación
     * 
     * @param string $login Login o email del usuario
     * @return array|null Usuario encontrado o null
     */
    public function findActiveByLogin($login) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (login = ? OR email = ?) 
                AND status = 'active' 
                LIMIT 1";
        
        $result = $this->query($sql, [$login, $login]);
        
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Obtener todos los usuarios por un rol específico
     */
    public function getUsersByRole($role) {
        $sql = "SELECT id, name, email, business_id FROM users WHERE role = ? AND status = 'active' ORDER BY name ASC";
        return $this->query($sql, [$role]);
    }

    /**
     * Obtener todos los usuarios por rol, sin importar su estado
     */
    public function getAllUsersByRole($role) {
        // MODIFICADO: Añadir login, start_date y end_date
        $sql = "SELECT id, login, name, email, status, start_date, end_date FROM users WHERE role = ? ORDER BY name ASC";
        return $this->query($sql, [$role]);
    }

    /**
     * Obtener los usuarios que ya están asignados a un formulario
     */
    public function getAssignedUsersByForm($formId) {
        $sql = "SELECT u.id, u.name, u.email 
                FROM users u
                INNER JOIN user_forms uf ON u.id = uf.user_id
                WHERE uf.form_id = ?
                ORDER BY u.name ASC";
        return $this->query($sql, [$formId]);
    }

    /**
     * NUEVO: Obtener los formularios asignados a un usuario específico
     */
    public function getAssignedForms($userId) {
        $sql = "SELECT f.id, f.title, f.status 
                FROM forms f
                INNER JOIN user_forms uf ON f.id = uf.form_id
                WHERE uf.user_id = ?
                ORDER BY f.title ASC";
        return $this->query($sql, [$userId]);
    }
}
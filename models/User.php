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
                creator.name as created_by_name
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
        $sql = "SELECT u.*, 
                b.name as business_name,
                creator.name as created_by_name
                FROM {$this->table} u
                LEFT JOIN businesses b ON u.business_id = b.id
                LEFT JOIN users creator ON u.created_by = creator.id
                WHERE u.id = ?";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
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
        return parent::delete($id);
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
                creator.name as created_by_name
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
        // Obtener usuario
        $user = $this->getById($id);
        
        if (!$user) {
            return ['can_delete' => false, 'reason' => 'Usuario no encontrado'];
        }
        
        // Los admin no se pueden eliminar entre ellos
        if ($user['role'] === 'admin') {
            return ['can_delete' => false, 'reason' => 'Los administradores no pueden ser eliminados'];
        }
        
        // Verificar si tiene respuestas (cuando se implemente)
        // TODO: Descomentar cuando exista la tabla responses
        /*
        $sqlResponses = "SELECT COUNT(*) as total FROM responses WHERE user_id = ?";
        $responses = $this->query($sqlResponses, [$id]);
        if ($responses[0]['total'] > 0) {
            return ['can_delete' => false, 'reason' => 'El usuario tiene respuestas asociadas'];
        }
        */
        
        // Verificar si tiene formularios asignados (cuando se implemente)
        // TODO: Descomentar cuando exista la tabla form_assignments
        /*
        $sqlForms = "SELECT COUNT(*) as total FROM form_assignments WHERE user_id = ?";
        $forms = $this->query($sqlForms, [$id]);
        if ($forms[0]['total'] > 0) {
            return ['can_delete' => false, 'reason' => 'El usuario tiene formularios asignados'];
        }
        */
        
        return ['can_delete' => true, 'reason' => ''];
    }
    
    /**
     * Obtener usuarios por empresa
     */
    public function getByBusiness($businessId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE business_id = ? 
                ORDER BY name ASC";
        
        return $this->query($sql, [$businessId]);
    }
    
    /**
     * Obtener usuarios por rol
     */
    public function getByRole($role) {
        $sql = "SELECT u.*, 
                b.name as business_name
                FROM {$this->table} u
                LEFT JOIN businesses b ON u.business_id = b.id
                WHERE u.role = ? 
                ORDER BY u.name ASC";
        
        return $this->query($sql, [$role]);
    }
    
    /**
     * Hash de contraseña
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    /**
     * Verificar contraseña
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Gestionar estados de usuarios encuestados según fechas
     * - Inactiva usuarios expirados (end_date < hoy)
     * - Inactiva usuarios pendientes (start_date > hoy)
     * - Activa usuarios en período válido (hoy entre start_date y end_date)
     * 
     * @return array Estadísticas de cambios
     */
    public function manageUserStatusByDates() {
        $today = date('Y-m-d');
        $stats = [
            'inactivated_expired' => 0,
            'inactivated_pending' => 0,
            'activated' => 0
        ];
        
        // 1. Inactivar usuarios EXPIRADOS (end_date < hoy)
        $sql = "UPDATE {$this->table} 
                SET status = 'inactive' 
                WHERE role = 'encuestado' 
                AND status = 'active' 
                AND end_date < ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$today]);
        $stats['inactivated_expired'] = $stmt->rowCount();
        
        // 2. Inactivar usuarios PENDIENTES (start_date > hoy)
        $sql = "UPDATE {$this->table} 
                SET status = 'inactive' 
                WHERE role = 'encuestado' 
                AND status = 'active' 
                AND start_date > ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$today]);
        $stats['inactivated_pending'] = $stmt->rowCount();
        
        // 3. Activar usuarios en PERÍODO VÁLIDO (start_date <= hoy <= end_date)
        $sql = "UPDATE {$this->table} 
                SET status = 'active' 
                WHERE role = 'encuestado' 
                AND status = 'inactive' 
                AND start_date <= ? 
                AND end_date >= ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$today, $today]);
        $stats['activated'] = $stmt->rowCount();
        
        return $stats;
    }
    
    /**
     * Inactivar usuarios encuestados cuya fecha de fin ya pasó
     * (Método legacy - usar manageUserStatusByDates)
     * 
     * @return int Número de usuarios inactivados
     */
    public function inactivateExpiredUsers() {
        $stats = $this->manageUserStatusByDates();
        return $stats['inactivated_expired'];
    }
    
    /**
     * Obtener usuarios encuestados que expiran hoy
     * 
     * @return array
     */
    public function getExpiringToday() {
        $today = date('Y-m-d');
        
        $sql = "SELECT u.*, b.name as business_name
                FROM {$this->table} u
                LEFT JOIN businesses b ON u.business_id = b.id
                WHERE u.role = 'encuestado' 
                AND u.status = 'active' 
                AND u.end_date = ?";
        
        return $this->query($sql, [$today]);
    }
    
    /**
     * Obtener estado calculado del usuario según fechas
     * 
     * @param array $user Usuario con start_date y end_date
     * @return string 'pending', 'active', 'expired'
     */
    public function getCalculatedStatus($user) {
        if ($user['role'] !== 'encuestado') {
            return $user['status'];
        }
        
        if (empty($user['start_date']) || empty($user['end_date'])) {
            return $user['status'];
        }
        
        $today = date('Y-m-d');
        $startDate = $user['start_date'];
        $endDate = $user['end_date'];
        
        if ($today < $startDate) {
            return 'pending';  // Aún no empieza
        } elseif ($today > $endDate) {
            return 'expired';  // Ya terminó
        } else {
            return 'active';   // En período válido
        }
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
}
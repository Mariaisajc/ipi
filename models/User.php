<?php
/**
 * IPI - Innovation Performance Index
 * Modelo: User
 * 
 * Gestión de usuarios del sistema
 */

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    /**
     * Buscar usuario por login o email
     * 
     * @param string $login
     * @return array|false
     */
    public function findByLogin($login) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (login = :login OR email = :email) 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'login' => $login,
            'email' => $login
        ]);
        
        return $stmt->fetch();
    }
    
    /**
     * Buscar usuario activo por login o email
     * 
     * @param string $login
     * @return array|false
     */
    public function findActiveByLogin($login) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (login = :login OR email = :email) 
                AND status = 'active' 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'login' => $login,
            'email' => $login
        ]);
        
        return $stmt->fetch();
    }
    
    /**
     * Buscar usuario por email
     * 
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        return $this->findBy(['email' => $email]);
    }
    
    /**
     * Crear nuevo usuario
     * 
     * @param array $data
     * @return int|string ID del usuario creado
     */
    public function createUser($data) {
        // Hash de la contraseña
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        // Establecer valores por defecto
        $data['status'] = $data['status'] ?? 'active';
        $data['role'] = $data['role'] ?? 'encuestado';
        
        return $this->create($data);
    }
    
    /**
     * Actualizar usuario
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser($id, $data) {
        // Si se actualiza la contraseña, hashearla
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            // Si no se proporciona contraseña, no actualizarla
            unset($data['password']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Obtener usuarios por rol
     * 
     * @param string $role
     * @return array
     */
    public function getUsersByRole($role) {
        return $this->all(['role' => $role, 'status' => 'active'], 'name ASC');
    }
    
    /**
     * Obtener usuarios de una empresa
     * 
     * @param int $businessId
     * @return array
     */
    public function getUsersByBusiness($businessId) {
        $sql = "SELECT u.*, b.name as business_name 
                FROM {$this->table} u
                LEFT JOIN businesses b ON u.business_id = b.id
                WHERE u.business_id = :business_id
                ORDER BY u.name ASC";
        
        return $this->query($sql, ['business_id' => $businessId]);
    }
    
    /**
     * Obtener todos los usuarios con información de empresa
     * 
     * @param array $filters Filtros opcionales
     * @return array
     */
    public function getAllWithBusiness($filters = []) {
        $sql = "SELECT u.*, b.name as business_name 
                FROM {$this->table} u
                LEFT JOIN businesses b ON u.business_id = b.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtrar por rol
        if (!empty($filters['role'])) {
            $sql .= " AND u.role = :role";
            $params['role'] = $filters['role'];
        }
        
        // Filtrar por estado
        if (!empty($filters['status'])) {
            $sql .= " AND u.status = :status";
            $params['status'] = $filters['status'];
        }
        
        // Filtrar por empresa
        if (!empty($filters['business_id'])) {
            $sql .= " AND u.business_id = :business_id";
            $params['business_id'] = $filters['business_id'];
        }
        
        // Búsqueda por texto
        if (!empty($filters['search'])) {
            $sql .= " AND (u.name LIKE :search OR u.login LIKE :search OR u.email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Cambiar estado de un usuario (activo/inactivo)
     * 
     * @param int $id
     * @return bool
     */
    public function toggleStatus($id) {
        $user = $this->find($id);
        
        if (!$user) {
            return false;
        }
        
        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        
        return $this->update($id, ['status' => $newStatus]);
    }
    
    /**
     * Actualizar fecha de último login
     * 
     * @param int $id
     * @return bool
     */
    public function updateLastLogin($id) {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Verificar si un login ya existe
     * 
     * @param string $login
     * @param int|null $exceptId ID a excluir de la búsqueda
     * @return bool
     */
    public function loginExists($login, $exceptId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE login = :login";
        
        if ($exceptId) {
            $sql .= " AND id != :except_id";
            $result = $this->query($sql, ['login' => $login, 'except_id' => $exceptId]);
        } else {
            $result = $this->query($sql, ['login' => $login]);
        }
        
        return $result[0]['count'] > 0;
    }
    
    /**
     * Verificar si un email ya existe
     * 
     * @param string $email
     * @param int|null $exceptId ID a excluir de la búsqueda
     * @return bool
     */
    public function emailExists($email, $exceptId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
        
        if ($exceptId) {
            $sql .= " AND id != :except_id";
            $result = $this->query($sql, ['email' => $email, 'except_id' => $exceptId]);
        } else {
            $result = $this->query($sql, ['email' => $email]);
        }
        
        return $result[0]['count'] > 0;
    }
    
    /**
     * Contar usuarios por rol
     * 
     * @return array
     */
    public function countByRole() {
        $sql = "SELECT role, COUNT(*) as count 
                FROM {$this->table} 
                WHERE status = 'active'
                GROUP BY role";
        
        $result = $this->query($sql);
        
        $counts = [
            'admin' => 0,
            'encuestado' => 0
        ];
        
        foreach ($result as $row) {
            $counts[$row['role']] = (int) $row['count'];
        }
        
        return $counts;
    }
    
    /**
     * Obtener usuarios encuestados sin empresa asignada
     * 
     * @return array
     */
    public function getEncuestadosWithoutBusiness() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE role = 'encuestado' 
                AND business_id IS NULL 
                AND status = 'active'
                ORDER BY name ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Asignar empresa a un usuario
     * 
     * @param int $userId
     * @param int $businessId
     * @return bool
     */
    public function assignBusiness($userId, $businessId) {
        return $this->update($userId, ['business_id' => $businessId]);
    }
    
    /**
     * Obtener estadísticas de usuarios
     * 
     * @return array
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN role = 'encuestado' THEN 1 ELSE 0 END) as encuestados
                FROM {$this->table}";
        
        $result = $this->query($sql);
        
        return $result[0];
    }
    
    /**
     * Cambiar contraseña
     * 
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Verificar contraseña actual
     * 
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function verifyPassword($userId, $password) {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        return password_verify($password, $user['password']);
    }
}
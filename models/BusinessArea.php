<?php
/**
 * BusinessArea Model
 * Gestión de áreas de negocio
 */

class BusinessArea extends Model {
    
    protected $table = 'business_areas';
    protected $primaryKey = 'id';
    
    /**
     * Obtener todas las áreas de una empresa
     */
    public function getByBusiness($businessId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE business_id = ? 
                ORDER BY name ASC";
        
        return $this->query($sql, [$businessId]);
    }
    
    /**
     * Crear nueva área de negocio
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (business_id, name, description, created_by, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $this->query($sql, [
            $data['business_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['created_by'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar área de negocio
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET name = ?, 
                    description = ?, 
                    updated_at = NOW()
                WHERE id = ?";
        
        $this->query($sql, [
            $data['name'],
            $data['description'] ?? null,
            $id
        ]);
        
        return true;
    }
    
    /**
     * Eliminar área
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->query($sql, [$id]);
        return true;
    }
    
    /**
     * Eliminar todas las áreas de una empresa
     */
    public function deleteByBusiness($businessId) {
        $sql = "DELETE FROM {$this->table} WHERE business_id = ?";
        $this->query($sql, [$businessId]);
        return true;
    }
    
    /**
     * Obtener área por ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE id = ?";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    /**
     * Contar áreas de una empresa
     */
    public function countByBusiness($businessId) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE business_id = ?";
        
        $result = $this->query($sql, [$businessId]);
        return $result[0]['total'] ?? 0;
    }
    
    /**
     * Verificar si el nombre existe en la empresa
     */
    public function nameExistsInBusiness($businessId, $name, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table} 
                WHERE business_id = ? AND name = ?";
        
        $params = [$businessId, $name];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->query($sql, $params);
        return !empty($result);
    }
}
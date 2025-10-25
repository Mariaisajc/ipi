<?php
/**
 * Business Model
 * Gestión de empresas en el sistema
 */

class Business extends Model {
    
    protected $table = 'businesses';
    protected $primaryKey = 'id';
    
    /**
     * Obtener todas las empresas activas
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT b.*, 
                COUNT(DISTINCT ba.id) as areas_count
                FROM {$this->table} b
                LEFT JOIN business_areas ba ON b.id = ba.business_id
                GROUP BY b.id
                ORDER BY b.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->query($sql);
    }
    
    /**
     * Obtener empresa por ID con relaciones
     */
    public function getById($id) {
        $sql = "SELECT b.*, 
                COUNT(DISTINCT ba.id) as areas_count
                FROM {$this->table} b
                LEFT JOIN business_areas ba ON b.id = ba.business_id
                WHERE b.id = ?
                GROUP BY b.id";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    /**
     * Crear nueva empresa
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (name, razon_social, nit, address, country, sector, subsector,
                 total_empleados, area_name, area_empleados, empleados_invitados,
                 tiene_departamento_innovacion, nivel_departamento_innovacion,
                 idiomas_participantes, idioma_informe, start_date, end_date,
                 administrador_nombre, administrador_email, status, created_by, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $this->query($sql, [
            $data['name'],
            $data['razon_social'] ?? null,
            $data['nit'] ?? null,
            $data['address'] ?? null,
            $data['country'] ?? null,
            $data['sector'] ?? null,
            $data['subsector'] ?? null,
            $data['total_empleados'] ?? null,
            $data['area_name'] ?? null,
            $data['area_empleados'] ?? null,
            $data['empleados_invitados'] ?? null,
            $data['tiene_departamento_innovacion'] ?? null,
            $data['nivel_departamento_innovacion'] ?? null,
            $data['idiomas_participantes'] ?? null,
            $data['idioma_informe'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['administrador_nombre'] ?? null,
            $data['administrador_email'] ?? null,
            $data['status'] ?? 'active',
            $data['created_by'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar empresa
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET name = ?, 
                    razon_social = ?,
                    nit = ?,
                    address = ?, 
                    country = ?, 
                    sector = ?,
                    subsector = ?,
                    total_empleados = ?,
                    area_name = ?,
                    area_empleados = ?,
                    empleados_invitados = ?,
                    tiene_departamento_innovacion = ?,
                    nivel_departamento_innovacion = ?,
                    idiomas_participantes = ?,
                    idioma_informe = ?,
                    start_date = ?,
                    end_date = ?,
                    administrador_nombre = ?,
                    administrador_email = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        $this->query($sql, [
            $data['name'],
            $data['razon_social'] ?? null,
            $data['nit'] ?? null,
            $data['address'] ?? null,
            $data['country'] ?? null,
            $data['sector'] ?? null,
            $data['subsector'] ?? null,
            $data['total_empleados'] ?? null,
            $data['area_name'] ?? null,
            $data['area_empleados'] ?? null,
            $data['empleados_invitados'] ?? null,
            $data['tiene_departamento_innovacion'] ?? null,
            $data['nivel_departamento_innovacion'] ?? null,
            $data['idiomas_participantes'] ?? null,
            $data['idioma_informe'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['administrador_nombre'] ?? null,
            $data['administrador_email'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
        
        return true;
    }
    
    /**
     * Eliminar empresa FÍSICAMENTE de la base de datos
     * IMPORTANTE: Solo llamar después de validar que:
     * - Status = 'borrador'
     * - No tiene respuestas asociadas
     */
    public function delete($id) {
        try {
            // PASO 1: Eliminar áreas de negocio asociadas
            $sql = "DELETE FROM business_areas WHERE business_id = ?";
            $this->query($sql, [$id]);
            
            // PASO 2: Eliminar la empresa
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $this->query($sql, [$id]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error al eliminar empresa ID {$id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Soft delete - Cambiar status a inactivo (alternativa)
     * Usar solo si se necesita mantener registro histórico
     */
    public function softDelete($id) {
        $sql = "UPDATE {$this->table} 
                SET status = 'inactive', updated_at = NOW() 
                WHERE id = ?";
        
        $this->query($sql, [$id]);
        return true;
    }
    
    /**
     * Buscar empresas
     */
    public function search($term) {
        $sql = "SELECT b.*, 
                COUNT(DISTINCT ba.id) as areas_count
                FROM {$this->table} b
                LEFT JOIN business_areas ba ON b.id = ba.business_id
                WHERE b.name LIKE ? OR b.razon_social LIKE ? OR b.nit LIKE ?
                GROUP BY b.id
                ORDER BY b.name ASC";
        
        $searchTerm = "%{$term}%";
        return $this->query($sql, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    /**
     * Filtrar empresas con múltiples criterios
     */
    public function filter($filters) {
        $sql = "SELECT b.*, 
                COUNT(DISTINCT ba.id) as areas_count
                FROM {$this->table} b
                LEFT JOIN business_areas ba ON b.id = ba.business_id
                WHERE 1=1";
        
        $params = [];
        
        // Búsqueda por texto
        if (!empty($filters['search'])) {
            $sql .= " AND (b.name LIKE ? OR b.razon_social LIKE ? OR b.nit LIKE ? OR b.sector LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Filtro por sector
        if (!empty($filters['sector'])) {
            $sql .= " AND b.sector = ?";
            $params[] = $filters['sector'];
        }
        
        // Filtro por país
        if (!empty($filters['country'])) {
            $sql .= " AND b.country = ?";
            $params[] = $filters['country'];
        }
        
        // Filtro por estado
        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " GROUP BY b.id ORDER BY b.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Contar total de empresas
     */
    public function countAll() {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result[0]['total'] ?? 0;
    }
    
    /**
     * Obtener áreas de negocio de una empresa
     */
    public function getAreas($businessId) {
        $sql = "SELECT * FROM business_areas 
                WHERE business_id = ? 
                ORDER BY name ASC";
        
        return $this->query($sql, [$businessId]);
    }
    
    /**
     * Verificar si el nombre ya existe
     */
    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table} 
                WHERE name = ?";
        
        $params = [$name];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->query($sql, $params);
        return !empty($result);
    }
    
    /**
     * Obtener empresas por estado
     */
    public function getByStatus($status) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = ? 
                ORDER BY name ASC";
        
        return $this->query($sql, [$status]);
    }
    
    /**
     * Obtener estadísticas de la empresa
     */
    public function getStats($businessId) {
        $sql = "SELECT 
                (SELECT COUNT(*) FROM business_areas WHERE business_id = ?) as areas,
                0 as surveys,
                0 as responses";
        
        $result = $this->query($sql, [$businessId]);
        return $result[0] ?? null;
    }
    
    /**
     * Verificar si la empresa tiene respuestas de encuestas
     * (Para validación de eliminación)
     */
    public function hasResponses($businessId) {
        // Cuando se implemente el módulo de encuestas, usar esta query:
        /*
        $sql = "SELECT COUNT(*) as total 
                FROM survey_responses sr
                JOIN surveys s ON sr.survey_id = s.id
                WHERE s.business_id = ?";
        
        $result = $this->query($sql, [$businessId]);
        return ($result[0]['total'] ?? 0) > 0;
        */
        
        // Por ahora, retornar false (no hay encuestas implementadas)
        return false;
    }
}
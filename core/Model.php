<?php
/**
 * IPI - Innovation Performance Index
 * Clase Base: Model
 * 
 * Clase padre para todos los modelos del sistema
 * Proporciona métodos comunes para interactuar con la base de datos
 */

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $timestamps = true;
    
    /**
     * Constructor - Establece conexión con la base de datos
     */
    public function __construct() {
        $this->db = self::getDbConnection();
    }

    /**
     * Obtiene la instancia única de la conexión PDO
     */
    public static function getDbConnection() {
        static $pdo = null;

        if ($pdo === null) {
            $config = require CONFIG_PATH . '/database.php';

            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                // Registrar error y lanzar excepción
                error_log("Error de conexión a BD: " . $e->getMessage());
                throw new Exception("No se pudo conectar a la base de datos");
            }
        }
        return $pdo;
    }
    
    /**
     * Obtener todos los registros
     * 
     * @param array $conditions Condiciones WHERE
     * @param string $orderBy Orden de resultados
     * @param int $limit Límite de registros
     * @return array
     */
    public function all($conditions = [], $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        // Agregar condiciones WHERE
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        // Agregar ORDER BY
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        // Agregar LIMIT
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Encontrar un registro por ID
     * 
     * @param mixed $id
     * @return array|false
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch();
    }
    
    /**
     * Encontrar el primer registro que coincida con las condiciones
     * 
     * @param array $conditions
     * @return array|false
     */
    public function findBy($conditions) {
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        
        return $stmt->fetch();
    }
    
    /**
     * Insertar un nuevo registro
     * 
     * @param array $data
     * @return int|string ID del registro insertado
     */
    public function create($data) {
        // Agregar timestamps si está habilitado
        if ($this->timestamps) {
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
        }
        
        $fields = array_keys($data);
        $values = ':' . implode(', :', $fields);
        $fields = implode(', ', $fields);
        
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$values})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar un registro
     * 
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        // Agregar timestamp de actualización
        if ($this->timestamps && !isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . 
               " WHERE {$this->primaryKey} = :id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    /**
     * Eliminar un registro
     * 
     * @param mixed $id
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Contar registros
     * 
     * @param array $conditions
     * @return int
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        $result = $stmt->fetch();
        
        return (int) $result['total'];
    }
    
    /**
     * Ejecutar una consulta SQL personalizada
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Iniciar una transacción
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollback() {
        return $this->db->rollBack();
    }
    
    /**
     * Obtener la última consulta SQL ejecutada (solo en desarrollo)
     */
    public function getLastQuery() {
        return $this->db->getAttribute(PDO::ATTR_STATEMENT_CLASS);
    }
}
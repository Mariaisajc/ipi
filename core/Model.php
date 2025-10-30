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
    
    private static $pdo_instance = null;

    /**
     * Constructor - Establece conexión con la base de datos
     */
    public function __construct() {
        $this->db = self::getDbConnection();
    }

    /**
     * Obtiene la instancia única de la conexión PDO (Singleton)
     */
    public static function getDbConnection() {
        if (self::$pdo_instance === null) {
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
                self::$pdo_instance = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                error_log("Error de conexión a BD: " . $e->getMessage());
                // En producción, no mostrar detalles del error.
                if (isset($GLOBALS['appConfig']['env']) && $GLOBALS['appConfig']['env'] === 'development') {
                    throw new Exception("No se pudo conectar a la base de datos: " . $e->getMessage());
                } else {
                    throw new Exception("No se pudo conectar a la base de datos.");
                }
            }
        }
        return self::$pdo_instance;
    }
    
    /**
     * Ejecutar una consulta SQL personalizada.
     * Acepta una conexión externa para poder participar en transacciones.
     *
     * @param string $sql La consulta SQL.
     * @param array $params Los parámetros para la consulta preparada.
     * @param PDO|null $db_connection Una conexión PDO externa. Si es null, usa la interna.
     * @return PDOStatement|array El resultado de la consulta.
     */
    public function query($sql, $params = [], $db_connection = null) {
        $db = $db_connection ?? $this->db;
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // Para SELECT, devuelve todos los resultados.
            if (strpos(strtoupper(trim($sql)), 'SELECT') === 0) {
                return $stmt->fetchAll();
            }
            
            // Para INSERT/UPDATE/DELETE, devuelve el statement para poder usar rowCount() o lastInsertId().
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error de consulta SQL: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e; // Relanzar la excepción para que la transacción pueda hacer rollback.
        }
    }

    /**
     * Encontrar un registro por su clave primaria.
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $result = $this->query($sql, [$id]);
        return $result ? $result[0] : null;
    }

    /**
     * Obtener todos los registros de la tabla.
     */
    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->query($sql);
    }

    /**
     * Devuelve el objeto de conexión PDO.
     */
    public function getDb() {
        return $this->db;
    }
}
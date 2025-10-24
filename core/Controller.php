<?php
/**
 * IPI - Innovation Performance Index
 * Clase Base: Controller
 * 
 * Clase padre para todos los controladores del sistema
 * Proporciona métodos comunes para manejar vistas, validaciones y respuestas
 */

class Controller {
    protected $view;
    protected $auth;
    protected $csrf;
    protected $validator;
    protected $config;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->view = new View();
        $this->auth = new Auth();
        $this->csrf = new CSRF();
        $this->validator = new Validator();
        $this->config = require CONFIG_PATH . '/app.php';
    }
    
    /**
     * Cargar una vista
     * 
     * @param string $view Ruta de la vista
     * @param array $data Datos para pasar a la vista
     * @param string $layout Layout a utilizar (null para sin layout)
     * @return void
     */
    protected function view($view, $data = [], $layout = null) {
        $this->view->render($view, $data, $layout);
    }
    
    /**
     * Cargar un modelo
     * 
     * @param string $model Nombre del modelo
     * @return object Instancia del modelo
     */
    protected function model($model) {
        $modelPath = MODELS_PATH . '/' . $model . '.php';
        
        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        }
        
        throw new Exception("Modelo {$model} no encontrado");
    }
    
    /**
     * Redireccionar a una URL
     * 
     * @param string $url URL relativa
     * @param array $params Parámetros GET opcionales
     * @return void
     */
    protected function redirect($url, $params = []) {
        $baseUrl = $this->config['url'];
        $url = ltrim($url, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        header("Location: {$baseUrl}/{$url}");
        exit;
    }
    
    /**
     * Redireccionar de vuelta a la página anterior
     * 
     * @return void
     */
    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? $this->config['url'];
        header("Location: {$referer}");
        exit;
    }
    
    /**
     * Establecer un mensaje flash en la sesión
     * 
     * @param string $type Tipo de mensaje (success, error, warning, info)
     * @param string $message Mensaje
     * @return void
     */
    protected function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Obtener y limpiar mensaje flash
     * 
     * @return array|null
     */
    protected function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
    
    /**
     * Responder con JSON
     * 
     * @param mixed $data Datos a devolver
     * @param int $status Código de estado HTTP
     * @return void
     */
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Validar request con CSRF
     * 
     * @return bool
     */
    protected function validateCSRF() {
        return $this->csrf->validate();
    }
    
    /**
     * Verificar si el request es POST
     * 
     * @return bool
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Verificar si el request es GET
     * 
     * @return bool
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Verificar si el request es AJAX
     * 
     * @return bool
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Obtener todos los datos del POST
     * 
     * @return array
     */
    protected function post() {
        return $_POST;
    }
    
    /**
     * Obtener un valor específico del POST
     * 
     * @param string $key Clave
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    protected function input($key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * Sanitizar input
     * 
     * @param string $data
     * @return string
     */
    protected function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar datos
     * 
     * @param array $data Datos a validar
     * @param array $rules Reglas de validación
     * @return array|bool Array de errores o true si es válido
     */
    protected function validate($data, $rules) {
        return $this->validator->validate($data, $rules);
    }
    
    /**
     * Verificar autenticación
     * 
     * @return bool
     */
    protected function isAuthenticated() {
        return $this->auth->check();
    }
    
    /**
     * Obtener usuario autenticado
     * 
     * @return array|null
     */
    protected function user() {
        return $this->auth->user();
    }
    
    /**
     * Verificar si el usuario tiene un rol específico
     * 
     * @param string $role
     * @return bool
     */
    protected function hasRole($role) {
        return $this->auth->hasRole($role);
    }
    
    /**
     * Requerir autenticación (middleware)
     * 
     * @param string|null $role Rol requerido
     * @return void
     */
    protected function requireAuth($role = null) {
        if (!$this->isAuthenticated()) {
            $this->setFlash('error', 'Debe iniciar sesión para acceder');
            $this->redirect('auth/login');
        }
        
        if ($role && !$this->hasRole($role)) {
            $this->setFlash('error', 'No tiene permisos para acceder a esta sección');
            $this->redirect('error/403');
        }
    }
    
    /**
     * Registrar log de actividad
     * 
     * @param string $message
     * @param string $level (info, warning, error)
     * @return void
     */
    protected function log($message, $level = 'info') {
        if ($this->config['logging']['enabled']) {
            $logFile = $this->config['logging']['path'] . date('Y-m-d') . '.log';
            $timestamp = date('Y-m-d H:i:s');
            $user = $this->user() ? $this->user()['login'] : 'guest';
            
            $logMessage = "[{$timestamp}] [{$level}] [{$user}] {$message}" . PHP_EOL;
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }
    }
}
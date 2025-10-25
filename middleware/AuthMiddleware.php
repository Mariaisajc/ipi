<?php
/**
 * IPI - Innovation Performance Index
 * Middleware: AuthMiddleware
 * 
 * Verificar que el usuario esté autenticado
 */

class AuthMiddleware {
    protected $auth;
    
    public function __construct() {
        $this->auth = new Auth();
    }
    
    /**
     * Verificar autenticación
     * 
     * @return bool
     */
    public function handle() {
        // Verificar si el usuario está autenticado
        if (!$this->auth->check()) {
            // Guardar la URL solicitada para redirigir después del login
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '';
            
            // Establecer mensaje flash
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Debe iniciar sesión para acceder a esta página'
            ];
            
            // Redirigir al login
            header('Location: ' . url('auth/login'));
            exit;
        }
        
        // Verificar si la sesión ha expirado
        if ($this->auth->isExpired()) {
            // Cerrar sesión
            $this->auth->logout();
            
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente'
            ];
            
            header('Location: ' . url('auth/login'));
            exit;
        }
        
        // Actualizar última actividad
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Obtener la URL a la que se intentaba acceder
     * 
     * @return string|null
     */
    public static function getIntendedUrl() {
        $url = $_SESSION['intended_url'] ?? null;
        unset($_SESSION['intended_url']);
        return $url;
    }
}
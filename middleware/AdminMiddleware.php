<?php
/**
 * IPI - Innovation Performance Index
 * Middleware: AdminMiddleware
 * 
 * Verificar que el usuario sea administrador
 */

class AdminMiddleware {
    protected $auth;
    
    public function __construct() {
        $this->auth = new Auth();
    }
    
    /**
     * Verificar que el usuario sea admin
     * 
     * @return bool
     */
    public function handle() {
        // Primero verificar autenticación
        $authMiddleware = new AuthMiddleware();
        $authMiddleware->handle();
        
        // Verificar que el usuario sea administrador
        if (!$this->auth->hasRole('admin')) {
            // Log del intento de acceso no autorizado
            $user = $this->auth->user();
            $logMessage = "Intento de acceso no autorizado al panel admin por usuario: " . 
                         ($user['login'] ?? 'desconocido');
            log_message($logMessage, 'warning');
            
            // Establecer mensaje flash
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'No tiene permisos para acceder a esta sección'
            ];
            
            // Redirigir según el rol
            if ($this->auth->hasRole('encuestado')) {
                header('Location: ' . url('survey/dashboard'));
            } else {
                header('Location: ' . url('error/403'));
            }
            exit;
        }
        
        return true;
    }
    
    /**
     * Verificar permisos específicos del administrador
     * (Para futuras implementaciones con permisos granulares)
     * 
     * @param array $permissions
     * @return bool
     */
    public function hasPermissions($permissions = []) {
        // Por ahora, todos los admins tienen todos los permisos
        // En el futuro se puede implementar un sistema de permisos más granular
        return $this->auth->hasRole('admin');
    }
}
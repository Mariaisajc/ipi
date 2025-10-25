<?php
/**
 * IPI - Innovation Performance Index
 * Middleware: SurveyMiddleware
 * 
 * Verificar que el usuario sea encuestado
 */

class SurveyMiddleware {
    protected $auth;
    
    public function __construct() {
        $this->auth = new Auth();
    }
    
    /**
     * Verificar que el usuario sea encuestado
     * 
     * @return bool
     */
    public function handle() {
        // Primero verificar autenticación
        $authMiddleware = new AuthMiddleware();
        $authMiddleware->handle();
        
        // Verificar que el usuario sea encuestado
        if (!$this->auth->hasRole('encuestado')) {
            // Log del intento de acceso
            $user = $this->auth->user();
            $logMessage = "Intento de acceso al panel de encuestas por usuario admin: " . 
                         ($user['login'] ?? 'desconocido');
            log_message($logMessage, 'info');
            
            // Establecer mensaje flash
            $_SESSION['flash'] = [
                'type' => 'info',
                'message' => 'Esta sección es solo para encuestados'
            ];
            
            // Redirigir según el rol
            if ($this->auth->hasRole('admin')) {
                header('Location: ' . url('admin/dashboard'));
            } else {
                header('Location: ' . url('error/403'));
            }
            exit;
        }
        
        return true;
    }
    
    /**
     * Verificar que el usuario tenga empresa asignada
     * 
     * @return bool
     */
    public function hasBusinessAssigned() {
        if (!$this->auth->check()) {
            return false;
        }
        
        $user = $this->auth->user();
        return !empty($user['business_id']);
    }
    
    /**
     * Verificar que el usuario tenga acceso a un formulario específico
     * 
     * @param int $formId
     * @return bool
     */
    public function hasFormAccess($formId) {
        if (!$this->auth->check()) {
            return false;
        }
        
        // Cargar modelo de usuario
        require_once MODELS_PATH . '/User.php';
        $userModel = new User();
        
        $userId = $this->auth->id();
        
        // Verificar si el usuario tiene asignado el formulario
        $sql = "SELECT COUNT(*) as count 
                FROM user_forms 
                WHERE user_id = :user_id AND form_id = :form_id";
        
        $result = $userModel->query($sql, [
            'user_id' => $userId,
            'form_id' => $formId
        ]);
        
        return $result[0]['count'] > 0;
    }
    
    /**
     * Verificar que el usuario esté dentro de las fechas permitidas
     * 
     * @return bool
     */
    public function isWithinAllowedDates() {
        if (!$this->auth->check()) {
            return false;
        }
        
        $user = $this->auth->user();
        $today = date('Y-m-d');
        
        // Si no tiene fechas configuradas, permitir acceso
        if (empty($user['start_date']) && empty($user['end_date'])) {
            return true;
        }
        
        // Verificar fecha de inicio
        if (!empty($user['start_date']) && $today < $user['start_date']) {
            return false;
        }
        
        // Verificar fecha de fin
        if (!empty($user['end_date']) && $today > $user['end_date']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Middleware completo con todas las verificaciones
     * 
     * @return bool
     */
    public function handleWithDateCheck() {
        // Verificar autenticación y rol
        $this->handle();
        
        // Verificar fechas
        if (!$this->isWithinAllowedDates()) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Su acceso al sistema no está disponible en este momento. Verifique las fechas asignadas.'
            ];
            
            header('Location: ' . url('survey/dashboard'));
            exit;
        }
        
        return true;
    }
}
<?php
/**
 * IPI - Innovation Performance Index
 * Clase: Auth
 * 
 * Sistema de autenticación y manejo de sesiones
 */

class Auth {
    protected $sessionKey = 'user';
    
    /**
     * Verificar si el usuario está autenticado
     * 
     * @return bool
     */
    public function check() {
        return isset($_SESSION[$this->sessionKey]) && !empty($_SESSION[$this->sessionKey]);
    }
    
    /**
     * Obtener el usuario autenticado
     * 
     * @return array|null
     */
    public function user() {
        return $_SESSION[$this->sessionKey] ?? null;
    }
    
    /**
     * Obtener ID del usuario autenticado
     * 
     * @return int|null
     */
    public function id() {
        return $this->check() ? $_SESSION[$this->sessionKey]['id'] : null;
    }
    
    /**
     * Iniciar sesión
     * 
     * @param string $login Usuario o email
     * @param string $password Contraseña
     * @return bool|array False si falla, array con usuario si tiene éxito
     */
    public function attempt($login, $password) {
        // Cargar modelo de usuario
        require_once MODELS_PATH . '/User.php';
        $userModel = new User();
        
        // Buscar usuario activo por login o email
        $user = $userModel->findActiveByLogin($login);
        
        if (!$user) {
            return false;
        }
        
        // Verificar contraseña
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        // Actualizar último login
        $userModel->updateLastLogin($user['id']);
        
        // Guardar usuario en sesión (sin contraseña)
        unset($user['password']);
        $_SESSION[$this->sessionKey] = $user;
        
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
        
        return $user;
    }
    
    /**
     * Iniciar sesión manualmente (sin verificar contraseña)
     * Útil para sistemas de login único o impersonación
     * 
     * @param array $user Datos del usuario
     * @return void
     */
    public function login($user) {
        unset($user['password']);
        $_SESSION[$this->sessionKey] = $user;
        session_regenerate_id(true);
    }
    
    /**
     * Cerrar sesión
     * 
     * @return void
     */
    public function logout() {
        unset($_SESSION[$this->sessionKey]);
        
        // Destruir la sesión completamente
        session_destroy();
        
        // Iniciar nueva sesión limpia
        session_start();
    }
    
    /**
     * Verificar si el usuario tiene un rol específico
     * 
     * @param string $role
     * @return bool
     */
    public function hasRole($role) {
        if (!$this->check()) {
            return false;
        }
        
        return $_SESSION[$this->sessionKey]['role'] === $role;
    }
    
    /**
     * Verificar si el usuario es administrador
     * 
     * @return bool
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }
    
    /**
     * Verificar si el usuario es encuestado
     * 
     * @return bool
     */
    public function isEncuestado() {
        return $this->hasRole('encuestado');
    }
    
    /**
     * Obtener el rol del usuario autenticado
     * 
     * @return string|null
     */
    public function role() {
        return $this->check() ? $_SESSION[$this->sessionKey]['role'] : null;
    }
    
    /**
     * Verificar si el usuario pertenece a una empresa específica
     * 
     * @param int $businessId
     * @return bool
     */
    public function belongsToBusiness($businessId) {
        if (!$this->check()) {
            return false;
        }
        
        return isset($_SESSION[$this->sessionKey]['business_id']) && 
               $_SESSION[$this->sessionKey]['business_id'] == $businessId;
    }
    
    /**
     * Obtener ID de la empresa del usuario
     * 
     * @return int|null
     */
    public function businessId() {
        return $this->check() ? ($_SESSION[$this->sessionKey]['business_id'] ?? null) : null;
    }
    
    /**
     * Hash de contraseña
     * 
     * @param string $password
     * @return string
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    /**
     * Verificar contraseña contra hash
     * 
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Actualizar datos del usuario en sesión
     * Útil después de actualizar perfil
     * 
     * @param array $data
     * @return void
     */
    public function updateSession($data) {
        if ($this->check()) {
            $_SESSION[$this->sessionKey] = array_merge($_SESSION[$this->sessionKey], $data);
        }
    }
    
    /**
     * Verificar si la sesión ha expirado
     * 
     * @param int $lifetime Tiempo de vida en segundos
     * @return bool
     */
    public function isExpired($lifetime = 7200) {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return false;
        }
        
        if (time() - $_SESSION['last_activity'] > $lifetime) {
            return true;
        }
        
        $_SESSION['last_activity'] = time();
        return false;
    }
}
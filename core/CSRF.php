<?php
/**
 * IPI - Innovation Performance Index
 * Clase: CSRF
 * 
 * Protección contra ataques Cross-Site Request Forgery (CSRF)
 */

class CSRF {
    protected $tokenName = 'csrf_token';
    protected $tokenExpire = 7200; // 2 horas
    
    /**
     * Generar un token CSRF
     * 
     * @return string
     */
    public function generateToken() {
        $token = bin2hex(random_bytes(32));
        
        $_SESSION[$this->tokenName] = $token;
        $_SESSION[$this->tokenName . '_time'] = time();
        
        return $token;
    }
    
    /**
     * Obtener el token actual o generar uno nuevo
     * 
     * @return string
     */
    public function getToken() {
        if (!isset($_SESSION[$this->tokenName]) || $this->isExpired()) {
            return $this->generateToken();
        }
        
        return $_SESSION[$this->tokenName];
    }
    
    /**
     * Validar el token CSRF
     * 
     * @param string|null $token Token a validar (si es null, se toma del POST)
     * @return bool
     */
    public function validate($token = null) {
        // Si no se proporciona token, intentar obtenerlo del POST
        if ($token === null) {
            $token = $_POST[$this->tokenName] ?? null;
        }
        
        // Si no hay token en la sesión, es inválido
        if (!isset($_SESSION[$this->tokenName])) {
            return false;
        }
        
        // Verificar si el token ha expirado
        if ($this->isExpired()) {
            return false;
        }
        
        // Comparar tokens usando hash_equals para prevenir timing attacks
        return hash_equals($_SESSION[$this->tokenName], $token);
    }
    
    /**
     * Verificar si el token ha expirado
     * 
     * @return bool
     */
    protected function isExpired() {
        if (!isset($_SESSION[$this->tokenName . '_time'])) {
            return true;
        }
        
        return (time() - $_SESSION[$this->tokenName . '_time']) > $this->tokenExpire;
    }
    
    /**
     * Generar campo hidden con el token CSRF
     * 
     * @return string HTML del campo hidden
     */
    public function field() {
        $token = $this->getToken();
        return '<input type="hidden" name="' . $this->tokenName . '" value="' . $token . '">';
    }
    
    /**
     * Obtener el token como meta tag (para AJAX)
     * 
     * @return string HTML del meta tag
     */
    public function metaTag() {
        $token = $this->getToken();
        return '<meta name="csrf-token" content="' . $token . '">';
    }
    
    /**
     * Invalidar el token actual
     * 
     * @return void
     */
    public function invalidate() {
        unset($_SESSION[$this->tokenName]);
        unset($_SESSION[$this->tokenName . '_time']);
    }
    
    /**
     * Regenerar token (útil después de operaciones sensibles)
     * 
     * @return string
     */
    public function regenerate() {
        $this->invalidate();
        return $this->generateToken();
    }
}
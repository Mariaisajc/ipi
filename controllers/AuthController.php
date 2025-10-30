<?php
/**
 * IPI - Innovation Performance Index
 * Controlador: AuthController
 * 
 * Manejo de autenticación (login/logout)
 */

class AuthController extends Controller {
    
    /**
     * Mostrar formulario de login
     */
    public function showLogin() {
        // Si ya está autenticado, redirigir al dashboard correspondiente
        if ($this->isAuthenticated()) {
            if ($this->hasRole('admin')) {
                $this->redirect('admin/dashboard');
            } else {
                $this->redirect('survey/dashboard');
            }
        }
        
        // Mostrar vista de login sin layout
        $this->view('auth/login', [], null);
    }
    
    /**
     * Procesar login
     */
    public function login() {
        // Verificar que sea POST
        if (!$this->isPost()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Validar CSRF
        if (!$this->validateCSRF()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Token de seguridad inválido'];
            $this->view('auth/login', [], null);
            return;
        }
        
        // Obtener datos del formulario
        $login = $this->input('login');
        $password = $this->input('password');
        $remember = $this->input('remember');
        
        // Validar campos requeridos
        if (empty($login) || empty($password)) {
            $flashMessage = ['type' => 'error', 'message' => 'Por favor ingrese usuario y contraseña'];
            $_SESSION['flash'] = $flashMessage;
            save_old(['login' => $login]);
            
            // Pasar el flash directamente a la vista también
            $this->view('auth/login', ['flash' => $flashMessage], null);
            return;
        }
        
        // Intentar autenticar
        $user = $this->auth->attempt($login, $password);
        
        if (!$user) {
            // Verificar si el usuario existe pero está inactivo
            $userModel = $this->model('User');
            $existingUser = $userModel->findByLogin($login);
            
            $flashMessage = null;
            if ($existingUser && $existingUser['status'] === 'inactive') {
                $flashMessage = ['type' => 'error', 'message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.'];
            } else {
                $flashMessage = ['type' => 'error', 'message' => 'Credenciales incorrectas. Por favor, verifica tus datos.'];
            }
            
            $_SESSION['flash'] = $flashMessage;
            save_old(['login' => $login]);
            
            // Log del intento fallido
            $this->log("Intento de login fallido para: {$login}", 'warning');
            
            // Pasar el flash directamente a la vista también
            $this->view('auth/login', ['flash' => $flashMessage], null);
            return;
        }
        
        // Login exitoso
        $this->log("Usuario {$user['login']} ha iniciado sesión", 'info');
        
        // Limpiar datos antiguos
        clear_old();
        clear_errors();
        
        // Configurar cookie "Remember Me" si fue seleccionado
        if ($remember) {
            $this->setRememberMeCookie($user['id']);
        }
        
        // Redirigir según el rol
        if ($user['role'] === 'admin') {
            $this->setFlash('success', 'Bienvenido, ' . $user['name']);
            $this->redirect('admin/dashboard');
        } else {
            $this->setFlash('success', 'Bienvenido, ' . $user['name']);
            $this->redirect('survey/dashboard');
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        $user = $this->user();
        
        if ($user) {
            $this->log("Usuario {$user['login']} ha cerrado sesión", 'info');
        }
        
        // Eliminar cookie "Remember Me"
        $this->clearRememberMeCookie();
        
        // Cerrar sesión
        $this->auth->logout();
        
        $this->setFlash('success', 'Has cerrado sesión correctamente');
        $this->redirect('auth/login');
    }
    
    /**
     * Verificar sesión expirada (AJAX)
     */
    public function checkSession() {
        if (!$this->isAjax()) {
            http_response_code(403);
            exit;
        }
        
        $this->json([
            'authenticated' => $this->isAuthenticated(),
            'expired' => $this->auth->isExpired()
        ]);
    }
    
    /**
     * Configurar cookie "Remember Me"
     * 
     * @param int $userId
     */
    protected function setRememberMeCookie($userId) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 días
        
        // Guardar token en sesión (en producción, usar tabla en BD)
        $_SESSION['remember_token'] = [
            'user_id' => $userId,
            'token' => $token,
            'expiry' => $expiry
        ];
        
        // Establecer cookie
        setcookie('remember_me', $token, $expiry, '/', '', false, true);
    }
    
    /**
     * Limpiar cookie "Remember Me"
     */
    protected function clearRememberMeCookie() {
        unset($_SESSION['remember_token']);
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    }
    
    /**
     * Verificar y procesar cookie "Remember Me"
     * Este método debería ser llamado al inicio de cada request
     */
    public function checkRememberMe() {
        // Si ya está autenticado, no hacer nada
        if ($this->isAuthenticated()) {
            return;
        }
        
        // Verificar si existe la cookie
        if (!isset($_COOKIE['remember_me'])) {
            return;
        }
        
        $token = $_COOKIE['remember_me'];
        
        // Verificar token en sesión (en producción, verificar en BD)
        if (!isset($_SESSION['remember_token'])) {
            $this->clearRememberMeCookie();
            return;
        }
        
        $rememberData = $_SESSION['remember_token'];
        
        // Verificar que el token coincida y no haya expirado
        if ($rememberData['token'] !== $token || time() > $rememberData['expiry']) {
            $this->clearRememberMeCookie();
            return;
        }
        
        // Token válido, autenticar automáticamente
        $userModel = $this->model('User');
        $user = $userModel->find($rememberData['user_id']);
        
        if ($user && $user['status'] === 'active') {
            $this->auth->login($user);
            $this->log("Usuario {$user['login']} autenticado automáticamente", 'info');
        } else {
            $this->clearRememberMeCookie();
        }
    }
}
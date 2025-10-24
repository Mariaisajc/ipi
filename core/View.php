<?php
/**
 * IPI - Innovation Performance Index
 * Clase: View
 * 
 * Motor de vistas para renderizar páginas HTML
 */

class View {
    protected $config;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->config = require CONFIG_PATH . '/app.php';
    }
    
    /**
     * Renderizar una vista con o sin layout
     * 
     * @param string $view Ruta de la vista (ejemplo: 'admin/dashboard')
     * @param array $data Datos para pasar a la vista
     * @param string|null $layout Layout a utilizar ('admin', 'survey', null)
     * @return void
     */
    public function render($view, $data = [], $layout = null) {
        // Extraer datos como variables
        extract($data);
        
        // Agregar configuración y helpers a las vistas
        $config = $this->config;
        $baseUrl = $this->config['url'];
        $auth = new Auth();
        $user = $auth->user();
        $csrf = new CSRF();
        
        // Obtener mensaje flash si existe
        $flash = $this->getFlash();
        
        // Ruta completa de la vista
        $viewPath = VIEWS_PATH . '/' . $view . '.php';
        
        // Verificar si la vista existe
        if (!file_exists($viewPath)) {
            throw new Exception("Vista no encontrada: {$view}");
        }
        
        // Si no hay layout, renderizar solo la vista
        if ($layout === null) {
            require $viewPath;
            return;
        }
        
        // Capturar el contenido de la vista en un buffer
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        
        // Renderizar con layout
        $layoutPath = VIEWS_PATH . '/layouts/' . $layout . '.php';
        
        if (!file_exists($layoutPath)) {
            throw new Exception("Layout no encontrado: {$layout}");
        }
        
        require $layoutPath;
    }
    
    /**
     * Renderizar un partial (fragmento de vista)
     * 
     * @param string $partial Ruta del partial
     * @param array $data Datos para el partial
     * @return void
     */
    public function partial($partial, $data = []) {
        extract($data);
        
        $partialPath = VIEWS_PATH . '/layouts/partials/' . $partial . '.php';
        
        if (file_exists($partialPath)) {
            require $partialPath;
        } else {
            echo "<!-- Partial no encontrado: {$partial} -->";
        }
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
     * Escapar HTML para prevenir XSS
     * 
     * @param string $string
     * @return string
     */
    public function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generar URL absoluta
     * 
     * @param string $path
     * @return string
     */
    public function url($path = '') {
        return rtrim($this->config['url'], '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Generar URL de asset (CSS, JS, imágenes)
     * 
     * @param string $path
     * @return string
     */
    public function asset($path) {
        $version = $this->config['assets']['version'];
        $url = rtrim($this->config['url'], '/') . '/assets/' . ltrim($path, '/');
        return $url . '?v=' . $version;
    }
}
<?php
/**
 * IPI - Innovation Performance Index
 * Clase: Router
 * 
 * Sistema de enrutamiento para procesar URLs y ejecutar controladores
 */

class Router {
    protected $routes;
    protected $config;
    protected $params = [];
    
    /**
     * Constructor
     * 
     * @param array $routes Array de rutas
     * @param array $config Configuración de la aplicación
     */
    public function __construct($routes, $config) {
        $this->routes = $routes;
        $this->config = $config;
    }
    
    /**
     * Despachar la ruta solicitada
     * 
     * @param string $url URL solicitada
     * @return void
     */
    public function dispatch($url) {
        // Limpiar la URL
        $url = $this->parseUrl($url);
        
        // Buscar coincidencia en las rutas
        $route = $this->matchRoute($url);
        
        if ($route === false) {
            // Ruta no encontrada
            $this->handleNotFound();
            return;
        }
        
        // Parsear el controlador y método
        list($controller, $method) = $this->parseRoute($route);
        
        // Cargar y ejecutar el controlador
        $this->executeController($controller, $method);
    }
    
    /**
     * Limpiar y parsear la URL
     * 
     * @param string $url
     * @return string
     */
    protected function parseUrl($url) {
        // Remover query string
        if (($pos = strpos($url, '?')) !== false) {
            $url = substr($url, 0, $pos);
        }
        
        // Limpiar la URL
        $url = trim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        return $url;
    }
    
    /**
     * Encontrar coincidencia de ruta
     * 
     * @param string $url
     * @return string|false
     */
    protected function matchRoute($url) {
        // Buscar coincidencia exacta primero
        if (isset($this->routes[$url])) {
            return $this->routes[$url];
        }
        
        // Buscar coincidencia con parámetros dinámicos
        foreach ($this->routes as $pattern => $route) {
            // Convertir parámetros {id} a expresiones regulares
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $pattern);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $url, $matches)) {
                // Guardar los parámetros capturados
                array_shift($matches); // Remover el primer elemento (la URL completa)
                $this->params = $matches;
                
                return $route;
            }
        }
        
        return false;
    }
    
    /**
     * Parsear la ruta en controlador y método
     * 
     * @param string $route
     * @return array
     */
    protected function parseRoute($route) {
        // Formato: 'Controller@method' o 'Namespace\Controller@method'
        $parts = explode('@', $route);
        
        if (count($parts) !== 2) {
            throw new Exception("Formato de ruta inválido: {$route}");
        }
        
        return $parts;
    }
    
    /**
     * Ejecutar el controlador y método
     * 
     * @param string $controllerName
     * @param string $method
     * @return void
     */
    protected function executeController($controllerName, $method) {
        // Reemplazar barras invertidas por directorios
        $controllerPath = str_replace('\\', '/', $controllerName);
        $controllerFile = CONTROLLERS_PATH . '/' . $controllerPath . '.php';
        
        // Verificar si el archivo existe
        if (!file_exists($controllerFile)) {
            throw new Exception("Controlador no encontrado: {$controllerName}");
        }
        
        // Cargar el controlador
        require_once $controllerFile;
        
        // Obtener el nombre de la clase (solo el nombre, sin namespace)
        $className = basename(str_replace('\\', '/', $controllerName));
        
        // Verificar si la clase existe
        if (!class_exists($className)) {
            throw new Exception("Clase de controlador no encontrada: {$className}");
        }
        
        // Crear instancia del controlador
        $controller = new $className();
        
        // Verificar si el método existe
        if (!method_exists($controller, $method)) {
            throw new Exception("Método no encontrado: {$className}@{$method}");
        }
        
        // Ejecutar el método con los parámetros
        call_user_func_array([$controller, $method], $this->params);
    }
    
    /**
     * Manejar ruta no encontrada (404)
     * 
     * @return void
     */
    protected function handleNotFound() {
        http_response_code(404);
        
        $errorFile = VIEWS_PATH . '/errors/404.php';
        
        if (file_exists($errorFile)) {
            require $errorFile;
        } else {
            echo "<h1>404 - Página no encontrada</h1>";
            echo "<p>La página que buscas no existe.</p>";
        }
        
        exit;
    }
    
    /**
     * Generar URL desde una ruta nombrada
     * 
     * @param string $name
     * @param array $params
     * @return string
     */
    public function url($name, $params = []) {
        $url = $this->config['url'] . '/' . $name;
        
        // Reemplazar parámetros en la URL
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        return $url;
    }
}
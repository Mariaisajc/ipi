<?php
/**
 * IPI - Innovation Performance Index
 * Front Controller - Punto de entrada de la aplicación
 * 
 * Todas las peticiones HTTP pasan por este archivo
 */

// Iniciar el buffer de salida
ob_start();

// Definir constantes de la aplicación PRIMERO
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('SERVICES_PATH', ROOT_PATH . '/services');
define('MIDDLEWARE_PATH', ROOT_PATH . '/middleware');
define('HELPERS_PATH', ROOT_PATH . '/helpers');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('EXPORTS_PATH', ROOT_PATH . '/exports');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Cargar Composer autoload si existe
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

// Cargar configuración de la aplicación
$appConfig = require CONFIG_PATH . '/app.php';
$dbConfig = require CONFIG_PATH . '/database.php';
$routes = require CONFIG_PATH . '/routes.php';

// Configurar zona horaria
date_default_timezone_set($appConfig['timezone']);

// Configurar sesión ANTES de iniciarla
ini_set('session.cookie_lifetime', $appConfig['session']['lifetime']);
ini_set('session.cookie_httponly', $appConfig['session']['httponly']);
ini_set('session.use_strict_mode', 1);

// Iniciar la sesión
session_start();

// Reportar errores según el entorno
if ($appConfig['env'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', STORAGE_PATH . '/logs/php-errors.log');
}

// Cargar archivos core manualmente (antes del autoload)
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/View.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/CSRF.php';
require_once CORE_PATH . '/Validator.php';

// Cargar helpers
require_once HELPERS_PATH . '/functions.php';

// Manejar errores y excepciones
set_exception_handler(function($exception) use ($appConfig) {
    if ($appConfig['env'] === 'development') {
        echo "<h1>Error Fatal</h1>";
        echo "<p><strong>Mensaje:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>Archivo:</strong> " . $exception->getFile() . "</p>";
        echo "<p><strong>Línea:</strong> " . $exception->getLine() . "</p>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    } else {
        // En producción, mostrar página de error genérica
        http_response_code(500);
        if (file_exists(VIEWS_PATH . '/errors/500.php')) {
            require VIEWS_PATH . '/errors/500.php';
        } else {
            echo "Ha ocurrido un error. Por favor, inténtelo más tarde.";
        }
        
        // Registrar el error en el log
        error_log(
            date('[Y-m-d H:i:s] ') . 
            $exception->getMessage() . ' en ' . 
            $exception->getFile() . ':' . 
            $exception->getLine() . "\n",
            3,
            STORAGE_PATH . '/logs/errors.log'
        );
    }
    exit;
});

// Crear instancia del router
$router = new Router($routes, $appConfig);

// Obtener la URL solicitada
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');

// Ejecutar el router
try {
    $router->dispatch($url);
} catch (Exception $e) {
    // Manejar errores de routing
    if ($appConfig['env'] === 'development') {
        echo "<h1>Error de Enrutamiento</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
    } else {
        http_response_code(404);
        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            require VIEWS_PATH . '/errors/404.php';
        } else {
            echo "Página no encontrada.";
        }
    }
}

// Enviar el buffer de salida
ob_end_flush();
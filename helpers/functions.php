<?php
/**
 * IPI - Innovation Performance Index
 * Funciones Auxiliares Globales
 * 
 * Este archivo contiene funciones helper que se pueden usar en toda la aplicación
 */

/**
 * Escapar HTML para prevenir XSS
 * 
 * @param string $string
 * @return string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generar URL completa
 * 
 * @param string $path
 * @return string
 */
function url($path = '') {
    $config = require CONFIG_PATH . '/app.php';
    return rtrim($config['url'], '/') . '/' . ltrim($path, '/');
}

/**
 * Generar URL de asset (CSS, JS, imágenes)
 * 
 * @param string $path
 * @return string
 */
function asset($path) {
    $config = require CONFIG_PATH . '/app.php';
    $version = $config['assets']['version'];
    $url = rtrim($config['url'], '/') . '/assets/' . ltrim($path, '/');
    return $url . '?v=' . $version;
}

/**
 * Redireccionar a una URL
 * 
 * @param string $url
 * @param int $statusCode
 * @return void
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . url($url), true, $statusCode);
    exit;
}

/**
 * Redireccionar de vuelta
 * 
 * @return void
 */
function back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? url('');
    header('Location: ' . $referer);
    exit;
}

/**
 * Obtener valor de un array de forma segura
 * 
 * @param array $array
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function array_get($array, $key, $default = null) {
    return $array[$key] ?? $default;
}

/**
 * Verificar si el usuario está autenticado
 * 
 * @return bool
 */
function is_auth() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

/**
 * Obtener usuario autenticado
 * 
 * @return array|null
 */
function auth_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Verificar si el usuario tiene un rol específico
 * 
 * @param string $role
 * @return bool
 */
function has_role($role) {
    if (!is_auth()) {
        return false;
    }
    return $_SESSION['user']['role'] === $role;
}

/**
 * Verificar si el usuario es admin
 * 
 * @return bool
 */
function is_admin() {
    return has_role('admin');
}

/**
 * Verificar si el usuario es encuestado
 * 
 * @return bool
 */
function is_encuestado() {
    return has_role('encuestado');
}

/**
 * Obtener y limpiar mensaje flash
 * 
 * @return array|null
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Establecer mensaje flash
 * 
 * @param string $type (success, error, warning, info)
 * @param string $message
 * @return void
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Mostrar mensaje flash HTML
 * 
 * @return string
 */
function show_flash() {
    $flash = get_flash();
    
    if (!$flash) {
        return '';
    }
    
    $alertClass = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $class = $alertClass[$flash['type']] ?? 'alert-info';
    
    $html = '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">';
    $html .= '<strong>' . ucfirst($flash['type']) . ':</strong> ' . e($flash['message']);
    $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Debug: imprimir y morir
 * 
 * @param mixed $data
 * @return void
 */
if (!function_exists('dd')) {
    function dd($data) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }
}

/**
 * Debug: imprimir sin morir
 * 
 * @param mixed $data
 * @return void
 */
if (!function_exists('dump')) {
    function dump($data) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
}

/**
 * Formatear fecha a español
 * 
 * @param string $date
 * @param string $format
 * @return string
 */
function format_date($date, $format = 'd/m/Y') {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formatear fecha y hora a español
 * 
 * @param string $datetime
 * @return string
 */
function format_datetime($datetime) {
    return format_date($datetime, 'd/m/Y H:i');
}

/**
 * Obtener tiempo transcurrido (hace X tiempo)
 * 
 * @param string $datetime
 * @return string
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'hace ' . $diff . ' segundos';
    } elseif ($diff < 3600) {
        return 'hace ' . floor($diff / 60) . ' minutos';
    } elseif ($diff < 86400) {
        return 'hace ' . floor($diff / 3600) . ' horas';
    } elseif ($diff < 604800) {
        return 'hace ' . floor($diff / 86400) . ' días';
    } else {
        return format_date($datetime);
    }
}

/**
 * Truncar texto
 * 
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Generar slug de texto
 * 
 * @param string $text
 * @return string
 */
function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Formatear número con separadores
 * 
 * @param float $number
 * @param int $decimals
 * @return string
 */
function format_number($number, $decimals = 0) {
    return number_format($number, $decimals, ',', '.');
}

/**
 * Formatear dinero
 * 
 * @param float $amount
 * @param string $currency
 * @return string
 */
function format_money($amount, $currency = 'COP') {
    $symbols = [
        'COP' => '$',
        'USD' => '$',
        'EUR' => '€'
    ];
    
    $symbol = $symbols[$currency] ?? '$';
    return $symbol . ' ' . format_number($amount, 2);
}

/**
 * Obtener valor antiguo de un campo (para repoblar formularios)
 * 
 * @param string $field
 * @param mixed $default
 * @return mixed
 */
function old($field, $default = '') {
    return $_SESSION['old'][$field] ?? $_POST[$field] ?? $default;
}

/**
 * Guardar valores antiguos en sesión
 * 
 * @param array $data
 * @return void
 */
function save_old($data) {
    $_SESSION['old'] = $data;
}

/**
 * Limpiar valores antiguos
 * 
 * @return void
 */
function clear_old() {
    unset($_SESSION['old']);
}

/**
 * Obtener error de validación de un campo
 * 
 * @param string $field
 * @return string|null
 */
function error($field) {
    return $_SESSION['errors'][$field][0] ?? null;
}

/**
 * Verificar si un campo tiene error
 * 
 * @param string $field
 * @return bool
 */
function has_error($field) {
    return isset($_SESSION['errors'][$field]);
}

/**
 * Guardar errores de validación en sesión
 * 
 * @param array $errors
 * @return void
 */
function save_errors($errors) {
    $_SESSION['errors'] = $errors;
}

/**
 * Limpiar errores de validación
 * 
 * @return void
 */
function clear_errors() {
    unset($_SESSION['errors']);
}

/**
 * Verificar si una ruta está activa (para navegación)
 * 
 * @param string $route
 * @return bool
 */
function is_active($route) {
    $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($currentUrl, $route) !== false;
}

/**
 * Generar clase CSS 'active' si la ruta coincide
 * 
 * @param string $route
 * @return string
 */
function active_class($route) {
    return is_active($route) ? 'active' : '';
}

/**
 * Generar token CSRF
 * 
 * @return string HTML del campo hidden
 */
function csrf_field() {
    $csrf = new CSRF();
    return $csrf->field();
}

/**
 * Obtener token CSRF
 * 
 * @return string
 */
function csrf_token() {
    $csrf = new CSRF();
    return $csrf->getToken();
}

/**
 * Registrar log
 * 
 * @param string $message
 * @param string $level
 * @return void
 */
function log_message($message, $level = 'info') {
    $config = require CONFIG_PATH . '/app.php';
    
    if ($config['logging']['enabled']) {
        $logFile = $config['logging']['path'] . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $user = auth_user() ? auth_user()['login'] : 'guest';
        
        $logMessage = "[{$timestamp}] [{$level}] [{$user}] {$message}" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}

/**
 * Generar UUID v4
 * 
 * @return string
 */
function generate_uuid() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Sanitizar string
 * 
 * @param string $string
 * @return string
 */
function sanitize($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

/**
 * Verificar si el request es AJAX
 * 
 * @return bool
 */
function is_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Responder con JSON
 * 
 * @param mixed $data
 * @param int $status
 * @return void
 */
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Obtener IP del cliente
 * 
 * @return string
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Generar contraseña aleatoria
 * 
 * @param int $length
 * @return string
 */
function generate_password($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle($chars), 0, $length);
}
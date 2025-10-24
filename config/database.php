<?php
/**
 * IPI - Innovation Performance Index
 * Configuración de Base de Datos
 * 
 * IMPORTANTE: Si tienes problemas de conexión, verifica:
 * 1. Que MySQL esté corriendo en XAMPP
 * 2. El puerto correcto (3306 por defecto, pero puede ser 3307 u otro)
 * 3. Que la base de datos 'innovacion_db' exista
 * 4. Usuario y contraseña correctos
 */

// Detectar el entorno automáticamente
$isProduction = (
    isset($_SERVER['HTTP_HOST']) && 
    strpos($_SERVER['HTTP_HOST'], 'innovationperfomanceinndex.com') !== false
);

// Configuración según el entorno
if ($isProduction) {
    // ========================================
    // CONFIGURACIÓN PRODUCCIÓN (HOSTINGER)
    // ========================================
    $config = [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'u123456789_innovacion', // CAMBIAR por tu base de datos real de Hostinger
        'username' => 'u123456789_admin', // CAMBIAR por tu usuario real de Hostinger
        'password' => '', // CAMBIAR por tu contraseña real de Hostinger
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    ];
} else {
    // ========================================
    // CONFIGURACIÓN DESARROLLO (XAMPP)
    // ========================================
    
    // Puerto de MySQL en XAMPP (SIEMPRE debe ser 3306 por defecto)
    $mysqlPort = '3306';
    
    $config = [
        'driver' => 'mysql',
        'host' => 'localhost', // Usar localhost en lugar de 127.0.0.1
        'port' => $mysqlPort,
        'database' => 'innovacion_db',
        'username' => 'root',
        'password' => '', // En XAMPP por defecto está vacío
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    ];
}

return $config;
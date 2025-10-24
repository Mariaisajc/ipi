<?php
/**
 * IPI - Innovation Performance Index
 * Configuración de Base de Datos
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
        'host' => 'localhost', // Hostinger usa localhost
        'port' => '3306',
        'database' => 'u123456789_innovacion', // Cambiar por tu base de datos real
        'username' => 'u123456789_admin', // Cambiar por tu usuario real
        'password' => '', // Cambiar por tu contraseña real
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
    $config = [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => '3306',
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
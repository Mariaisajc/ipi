<?php
/**
 * IPI - Innovation Performance Index
 * Configuración General de la Aplicación
 */

return [
    // Información de la aplicación
    'name' => 'IPI - Innovation Performance Inndex',
    'version' => '1.0.0',
    'env' => 'development', // development, production
    
    // URL base de la aplicación
    'url' => 'http://localhost/ipi',
    
    // Configuración de zona horaria
    'timezone' => 'America/Bogota',
    
    // Configuración de idioma
    'locale' => 'es',
    'fallback_locale' => 'en',
    
    // Configuración de sesiones
    'session' => [
        'name' => 'IPI_SESSION',
        'lifetime' => 7200, // 2 horas en segundos
        'path' => '/',
        'domain' => '',
        'secure' => false, // true en producción con HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ],
    
    // Configuración de seguridad
    'security' => [
        'csrf_token_name' => 'csrf_token',
        'csrf_expire' => 7200, // 2 horas
        'password_min_length' => 6,
        'password_require_special_char' => false
    ],
    
    // Configuración de uploads
    'uploads' => [
        'path' => __DIR__ . '/../uploads/',
        'max_size' => 10485760, // 10MB en bytes
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx']
    ],
    
    // Configuración de exportaciones
    'exports' => [
        'path' => __DIR__ . '/../exports/',
        'temp_path' => __DIR__ . '/../exports/temp/',
        'formats' => ['excel', 'csv'],
        'auto_delete_after' => 3600 // 1 hora en segundos
    ],
    
    // Configuración de logs
    'logging' => [
        'enabled' => true,
        'path' => __DIR__ . '/../storage/logs/',
        'level' => 'debug', // debug, info, warning, error
        'max_files' => 30 // Días de retención
    ],
    
    // Configuración de cache
    'cache' => [
        'enabled' => false,
        'path' => __DIR__ . '/../storage/cache/',
        'lifetime' => 3600 // 1 hora
    ],
    
    // Configuración de paginación
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100
    ],
    
    // Rutas públicas (sin autenticación)
    'public_routes' => [
        'auth/login',
        'auth/logout',
        'error/404',
        'error/403'
    ],
    
    // Roles del sistema
    'roles' => [
        'admin' => 'Administrador',
        'encuestado' => 'Encuestado'
    ],
    
    // Assets CDN (opcional)
    'assets' => [
        'use_cdn' => false,
        'cdn_url' => '',
        'version' => '1.0.0' // Para cache busting
    ]
];
<?php
/**
 * Script: Gestionar Estados de Usuarios Encuestados por Fechas
 * 
 * Este script debe ejecutarse diariamente mediante un cron job para:
 * 1. Inactivar usuarios cuya fecha de fin ya pasó (expirados)
 * 2. Inactivar usuarios cuya fecha de inicio no ha llegado (pendientes)
 * 3. Activar usuarios que están en su período válido
 * 
 * Configuración Cron (ejecutar diariamente a las 00:01):
 * 1 0 * * * php /ruta/al/proyecto/cron/inactivate_expired_users.php
 */

// Cargar el framework
require_once __DIR__ . '/../index.php';

try {
    // Conectar a la base de datos
    $config = require CONFIG_PATH . '/database.php';
    
    $dsn = sprintf(
        "%s:host=%s;port=%s;dbname=%s;charset=%s",
        $config['driver'],
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    // Obtener fecha actual
    $today = date('Y-m-d');
    $stats = [
        'inactivated_expired' => 0,
        'inactivated_pending' => 0,
        'activated' => 0
    ];
    
    // 1. Inactivar usuarios EXPIRADOS (end_date < hoy)
    $sql = "UPDATE users 
            SET status = 'inactive' 
            WHERE role = 'encuestado' 
            AND status = 'active' 
            AND end_date < ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today]);
    $stats['inactivated_expired'] = $stmt->rowCount();
    
    // 2. Inactivar usuarios PENDIENTES (start_date > hoy)
    $sql = "UPDATE users 
            SET status = 'inactive' 
            WHERE role = 'encuestado' 
            AND status = 'active' 
            AND start_date > ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today]);
    $stats['inactivated_pending'] = $stmt->rowCount();
    
    // 3. Activar usuarios en PERÍODO VÁLIDO (start_date <= hoy <= end_date)
    $sql = "UPDATE users 
            SET status = 'active' 
            WHERE role = 'encuestado' 
            AND status = 'inactive' 
            AND start_date <= ? 
            AND end_date >= ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today, $today]);
    $stats['activated'] = $stmt->rowCount();
    
    // Log del resultado
    $logMessage = sprintf(
        "%s - Expirados: %d | Pendientes: %d | Activados: %d\n",
        date('Y-m-d H:i:s'),
        $stats['inactivated_expired'],
        $stats['inactivated_pending'],
        $stats['activated']
    );
    
    file_put_contents(__DIR__ . '/logs/inactivate_users.log', $logMessage, FILE_APPEND);
    
    echo "Proceso completado:\n";
    echo "- Usuarios expirados inactivados: {$stats['inactivated_expired']}\n";
    echo "- Usuarios pendientes inactivados: {$stats['inactivated_pending']}\n";
    echo "- Usuarios activados: {$stats['activated']}\n";
    
} catch (PDOException $e) {
    $errorMessage = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/logs/inactivate_users_error.log', $errorMessage, FILE_APPEND);
    
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
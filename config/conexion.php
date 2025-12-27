<?php
/**
 * CONEXIÓN SEGURA A BASE DE DATOS
 * ================================
 * Implementa buenas prácticas de seguridad y manejo de errores
 */

// Cargar configuración de branding y seguridad
require_once __DIR__ . '/branding.php';

// Configuración de errores (NO mostrar en pantalla en producción)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errors.log');
error_reporting(E_ALL);

// Credenciales (Idealmente desde variables de entorno)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'basedatos_barberiapro');

// Configurar reporte de errores de MySQLi (más control)
mysqli_report(MYSQLI_REPORT_OFF);

$conn = null;

try {
    $conn = new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME
    );
    
    // Verificar errores de conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión a base de datos: " . $conn->connect_error);
    }
    
    // Establecer charset UTF-8 para caracteres especiales
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error al configurar charset: " . $conn->error);
    }
    
    // Configurar variables de sesión de MySQL
    $conn->query("SET SESSION max_connections_per_hour = 100");
    
} catch (Exception $e) {
    // Registrar error en log
    error_log("[" . date('Y-m-d H:i:s') . "] BD Error: " . $e->getMessage());
    
    // NO exponemos detalles técnicos al cliente
    $conn = null;
    
    // Si es una página que necesita BD, redirigir a error
    // (Se maneja en cada página según su lógica)
}

/**
 * Función auxiliar para ejecutar consultas preparadas de forma segura
 */
function ejecutarConsultaSegura($conn, $sql, $params = [], $tipos = '') {
    try {
        if (!$conn) {
            throw new Exception("Conexión a base de datos no disponible");
        }
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en preparación: " . $conn->error);
        }
        
        // Bindear parámetros si existen
        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error en ejecución: " . $stmt->error);
        }
        
        return $stmt;
    } catch (Exception $e) {
        error_log("[" . date('Y-m-d H:i:s') . "] Query Error: " . $e->getMessage());
        return null;
    }
}

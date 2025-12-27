<?php
// Archivo: BarberiaVictorBarberClub/controllers/verificar_disponibilidad.php

// 1. SILENCIAR ERRORES VISUALES (CRÍTICO PARA JSON)
error_reporting(E_ALL); // Reportar internamente
ini_set('display_errors', 0); // No mostrar nada al navegador
ini_set('log_errors', 1); // Guardar en log del servidor

// 2. BUFFER DE SALIDA
ob_start();

header('Content-Type: application/json; charset=utf-8');

$response = ['bloqueado' => false, 'mensaje' => '', 'ocupados' => []];

try {
    // 3. INCLUIR CONEXIÓN
    $ruta_conexion = "../config/conexion.php";
    if (!file_exists($ruta_conexion)) {
        throw new Exception("No se encuentra el archivo de conexión.");
    }
    require_once $ruta_conexion;

    // Verificar conexión real
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Error al conectar con la base de datos.");
    }

    // 4. VALIDAR INPUTS
    $fecha = $_GET['fecha'] ?? '';
    $id_barbero = isset($_GET['id_barbero']) ? intval($_GET['id_barbero']) : 0;

    if (empty($fecha) || $id_barbero <= 0) {
        // Si faltan datos, devolvemos vacío pero no error fatal
        echo json_encode($response);
        exit;
    }

    // --- A. VERIFICAR CIERRE GENERAL (Feriados/Bloqueos) ---
    // Intentamos verificar la tabla 'bloqueos_dias' (la que usamos en configuracion.php)
    // Usamos try-catch interno por si la tabla no existe aún
    try {
        $stmt = $conn->prepare("SELECT motivo FROM bloqueos_dias WHERE fecha = ?");
        if ($stmt) {
            $stmt->bind_param("s", $fecha);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $response['bloqueado'] = true;
                $response['mensaje'] = "Cerrado: " . $row['motivo'];
                echo json_encode($response);
                exit;
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        // Si falla la tabla bloqueos_dias, ignoramos y seguimos (para no romper el flujo)
    }

    // --- B. VERIFICAR AUSENCIA DEL BARBERO ---
    $stmt = $conn->prepare("SELECT motivo FROM ausencias WHERE id_barbero = ? AND ? BETWEEN fecha_inicio AND COALESCE(fecha_fin, fecha_inicio)");
    if (!$stmt) throw new Exception("Error preparando consulta ausencias: " . $conn->error);
    
    $stmt->bind_param("is", $id_barbero, $fecha);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $response['bloqueado'] = true;
        $response['mensaje'] = "No disponible: " . $row['motivo'];
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // --- C. OBTENER HORARIOS OCUPADOS ---
    // Buscamos reservas 'activa' o 'completada'. Ignoramos 'cancelada'.
    $ocupados = [];
    $sql_reservas = "SELECT DATE_FORMAT(fecha_hora, '%H:%i') as hora 
                     FROM reservas 
                     WHERE id_barbero = ? 
                     AND DATE(fecha_hora) = ? 
                     AND estado != 'cancelada'"; // Bloqueamos todo lo que no esté cancelado
                     
    $stmt = $conn->prepare($sql_reservas);
    if (!$stmt) throw new Exception("Error preparando consulta reservas.");

    $stmt->bind_param("is", $id_barbero, $fecha);
    $stmt->execute();
    $res = $stmt->get_result();
    
    while ($row = $res->fetch_assoc()) {
        $ocupados[] = $row['hora'];
    }
    $response['ocupados'] = $ocupados;

} catch (Exception $e) {
    // En caso de error, devolvemos un JSON válido con el error (útil para debug)
    // En producción, podrías poner un mensaje genérico
    $response['bloqueado'] = true; // Bloquear para evitar reservas en error
    $response['mensaje'] = "Error del sistema: " . $e->getMessage();
}

// LIMPIEZA FINAL Y SALIDA
if (ob_get_length()) ob_clean(); // Borrar cualquier basura que se haya colado
echo json_encode($response);
if (isset($conn)) $conn->close();
?>
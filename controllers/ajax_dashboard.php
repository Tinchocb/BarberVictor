<?php
/**
 * AJAX DASHBOARD - LOGICA FINAL
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_end_clean(); 
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['acceso_autorizado'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$desde = $_POST['desde'] ?? date('Y-m-d');
$hasta = $_POST['hasta'] ?? date('Y-m-d');
$fecha_inicio = "$desde 00:00:00";
$fecha_fin    = "$hasta 23:59:59";

$response = [
    'kpi' => ['total_turnos' => 0, 'pendientes' => 0, 'ausentes' => 0, 'ingresos' => 0],
    'proximo' => null,
    'grafico' => ['labels' => [], 'ingresos' => []],
    'tabla' => []
];

try {
    if (!isset($conn) || $conn->connect_error) { throw new Exception("Error DB"); }

    // 1. KPIs (Respetan el filtro de fechas seleccionado)
    $sqlStats = "SELECT 
                    COUNT(*) as total, 
                    SUM(CASE WHEN estado = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                    SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as pendientes
                 FROM reservas 
                 WHERE fecha_hora BETWEEN ? AND ?";
                 
    $stmt = $conn->prepare($sqlStats);
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    
    $response['kpi']['total_turnos'] = $data['total'] ?? 0;
    $response['kpi']['ausentes']     = $data['ausentes'] ?? 0;
    $response['kpi']['pendientes']   = $data['pendientes'] ?? 0;
    $stmt->close();

    // 2. Ingresos (Solo completados)
    $stmt = $conn->prepare("SELECT SUM(s.precio) as total FROM reservas r JOIN servicios s ON r.servicio = s.nombre WHERE r.fecha_hora BETWEEN ? AND ? AND r.estado = 'completada'");
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $response['kpi']['ingresos'] = $data['total'] ?? 0;
    $stmt->close();

    // 3. PRÓXIMO TURNO (Global: El siguiente en la línea de tiempo desde HOY)
    // Usamos CURDATE() para que si son las 14:00 y el turno era 13:30 pero sigue activo, LO MUESTRE.
    $sqlNext = "SELECT r.fecha_hora, r.cliente, r.servicio, r.telefono, b.nombre as barbero 
                FROM reservas r 
                JOIN barberos b ON r.id_barbero = b.id_barbero 
                WHERE r.fecha_hora >= CURDATE() AND r.estado = 'activa' 
                ORDER BY r.fecha_hora ASC LIMIT 1";
    
    $resNext = $conn->query($sqlNext);
    if ($resNext && $resNext->num_rows > 0) {
        $next = $resNext->fetch_assoc();
        $response['proximo'] = [
            'hora' => date('H:i', strtotime($next['fecha_hora'])),
            'fecha' => date('d/m', strtotime($next['fecha_hora'])),
            'cliente' => $next['cliente'],
            'telefono' => $next['telefono'],
            'servicio' => $next['servicio'],
            'barbero' => $next['barbero']
        ];
    }

    // 4. Gráfico
    $stmt = $conn->prepare("SELECT DATE(r.fecha_hora) as dia, SUM(s.precio) as total FROM reservas r JOIN servicios s ON r.servicio = s.nombre WHERE r.fecha_hora BETWEEN ? AND ? AND r.estado = 'completada' GROUP BY dia ORDER BY dia ASC");
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) {
        $response['grafico']['labels'][] = date('d/m', strtotime($row['dia']));
        $response['grafico']['ingresos'][] = $row['total'];
    }
    $stmt->close();

    // 5. Tabla (Incluye teléfono)
    $stmt = $conn->prepare("SELECT r.id, r.fecha_hora, r.cliente, r.id_cliente as dni, r.telefono, r.servicio, r.estado, b.nombre as barbero 
    FROM reservas r 
    JOIN barberos b ON r.id_barbero = b.id_barbero 
    WHERE r.fecha_hora BETWEEN ? AND ? 
    ORDER BY r.fecha_hora ASC");
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()) {
$response['tabla'][] = [
'id' => $row['id'],
'fecha' => date('d/m/Y', strtotime($row['fecha_hora'])),
'hora' => date('H:i', strtotime($row['fecha_hora'])),
'cliente' => $row['cliente'],
'dni' => $row['dni'], // DNI Agregado
'telefono' => $row['telefono'],
'barbero' => $row['barbero'],
'servicio' => $row['servicio'],
'estado' => $row['estado']
];
}
$stmt->close();

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]); exit;
}

echo json_encode($response);
$conn->close();
?>
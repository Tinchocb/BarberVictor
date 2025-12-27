<?php
// Archivo: controllers/cancelar_reserva.php
require_once "../config/conexion.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    // Validamos que exista y esté activa antes de cancelar
    $stmt = $conn->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = ? AND estado = 'activa'");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo cancelar o ya estaba cancelada']);
    }
    $stmt->close();
}
$conn->close();
?>
<?php
// Archivo: controllers/update_estado.php
require_once "../config/conexion.php";

// Encabezado JSON para que JS entienda la respuesta
header('Content-Type: application/json');

// Verificar sesión (Seguridad)
session_start();
if (!isset($_SESSION['acceso_autorizado'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $estado = isset($_POST['estado']) ? $_POST['estado'] : '';

    // Validar estado permitido
    $estados_validos = ['activa', 'completada', 'ausente', 'cancelada'];
    
    if ($id > 0 && in_array($estado, $estados_validos)) {
        
        $stmt = $conn->prepare("UPDATE reservas SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $estado, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar DB']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
$conn->close();
?>
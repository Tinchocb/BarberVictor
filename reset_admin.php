<?php
/**
 * RESET ADMIN - SCRIPT DE RECUPERACIÓN DE ACCESO
 * ===============================================
 * CORREGIDO: Adaptado para la estructura real de la tabla 'usuarios'
 */

require_once "config/conexion.php";

$status = "";

try {
    // Configuración del usuario administrativo
    $usuario = "barbero";  
    $password_real = "0000";
    $password_encriptada = password_hash($password_real, PASSWORD_DEFAULT);
    $email = "admin@barberiapro.com";
    // Eliminamos $nombre porque la columna no existe en tu BD

    if (!$conn) {
        throw new Exception("No hay conexión a la base de datos.");
    }

    // 1. Intentar actualizar si ya existe el usuario 'barbero'
    // Quitamos 'nombre = ?' de la consulta
    $stmt_update = $conn->prepare("UPDATE usuarios SET password = ?, email = ? WHERE usuario = ?");
    if (!$stmt_update) {
        throw new Exception("Error en prepare UPDATE: " . $conn->error);
    }
    
    $stmt_update->bind_param("sss", $password_encriptada, $email, $usuario);
    $stmt_update->execute();
    
    if ($stmt_update->affected_rows > 0) {
        $status = "✅ Contraseña restablecida correctamente para 'barbero'.";
    } else {
        // 2. Si no se actualizó nada, intentamos crear el usuario
        // Verificar si existe para no duplicar (usando prepared statement - SEGURO)
        $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $check_stmt->bind_param("s", $usuario);
        $check_stmt->execute();
        $check = $check_stmt->get_result();
        
        if ($check && $check->num_rows > 0) {
            $status = "✅ Usuario 'barbero' ya existe. Contraseña actualizada.";
            $check_stmt->close();
        } else {
            $check_stmt->close();
            // Usuario no existía, crearlo
            $stmt_insert = $conn->prepare("INSERT INTO usuarios (usuario, email, password) VALUES (?, ?, ?)");
            if (!$stmt_insert) {
                throw new Exception("Error en prepare INSERT: " . $conn->error);
            }
            
            $stmt_insert->bind_param("sss", $usuario, $email, $password_encriptada);
            
            if ($stmt_insert->execute()) {
                $status = "✅ Usuario 'barbero' creado correctamente con credenciales.";
            } else {
                throw new Exception("Error al crear usuario: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        }
    }
    $stmt_update->close();
    
} catch (Exception $e) {
    $status = "❌ Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin</title>
    <style>
        body { font-family: sans-serif; background: #050505; color: #fff; padding: 40px; text-align: center; }
        .container { max-width: 500px; margin: 0 auto; background: #1a1a1a; padding: 40px; border-radius: 12px; border: 1px solid #333; }
        h1 { color: #C5A059; }
        .credentials { background: rgba(16, 185, 129, 0.15); padding: 20px; border-radius: 8px; margin: 20px 0; text-align: left; border-left: 3px solid #10b981; }
        .credential-item { margin: 10px 0; font-family: monospace; font-size: 1.1em; }
        a { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #C5A059; color: #000; text-decoration: none; border-radius: 6px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Resultado</h1>
        <p><?= htmlspecialchars($status) ?></p>
        
        <div class="credentials">
            <div class="credential-item">👤 Usuario: <strong><?= htmlspecialchars($usuario) ?></strong></div>
            <div class="credential-item">🔑 Contraseña: <strong><?= htmlspecialchars($password_real) ?></strong></div>
        </div>
        
        <a href="login.php">Ir al Login</a>
    </div>
</body>
</html>
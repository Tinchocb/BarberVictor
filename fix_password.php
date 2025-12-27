<?php
/**
 * FIX CONTRASEÑA - ACTUALIZAR PASSWORD BARBERO
 * ==============================================
 */

require_once __DIR__ . "/config/conexion.php";

$status = "";
$password_real = "0000";
$password_hash = password_hash($password_real, PASSWORD_DEFAULT);
$usuario = "barbero";

echo "<h1>🔐 ACTUALIZACIÓN DE CONTRASEÑA</h1>";
echo "<hr>";

if (!$conn) {
    echo "❌ Sin conexión a BD<br>";
    exit;
}

// 1. Verificar contraseña actual
echo "<h2>Estado actual:</h2>";
$check = $conn->prepare("SELECT password FROM usuarios WHERE usuario = ?");
$check->bind_param("s", $usuario);
$check->execute();
$result = $check->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_pass = $row['password'];
    echo "✅ Usuario 'barbero' encontrado<br>";
    echo "Hash actual: <code>" . htmlspecialchars($current_pass) . "</code><br><br>";
    
    // Probar password_verify
    $verify_test = password_verify($password_real, $current_pass);
    echo "<strong>Prueba password_verify('0000', hash):</strong> ";
    echo ($verify_test ? "✅ CORRECTO" : "❌ FALLO");
    echo "<br><br>";
} else {
    echo "❌ Usuario 'barbero' no encontrado<br>";
    exit;
}

// 2. Actualizar contraseña
echo "<h2>Actualizando contraseña...</h2>";
$update = $conn->prepare("UPDATE usuarios SET password = ? WHERE usuario = ?");
$update->bind_param("ss", $password_hash, $usuario);

if ($update->execute()) {
    echo "✅ Contraseña actualizada exitosamente<br>";
    echo "Nueva contraseña: <code>0000</code><br>";
    echo "Nuevo hash: <code>" . htmlspecialchars($password_hash) . "</code><br><br>";
    
    // 3. Verificar que se guardó bien
    echo "<h2>Verificación post-actualización:</h2>";
    $check2 = $conn->prepare("SELECT password FROM usuarios WHERE usuario = ?");
    $check2->bind_param("s", $usuario);
    $check2->execute();
    $result2 = $check2->get_result();
    
    if ($result2 && $result2->num_rows > 0) {
        $row2 = $result2->fetch_assoc();
        $verify_test2 = password_verify($password_real, $row2['password']);
        echo "Prueba password_verify('0000', nuevo_hash): ";
        echo ($verify_test2 ? "✅ CORRECTO" : "❌ FALLO");
        echo "<br><br>";
    }
    
    echo "✅ <strong>Contraseña de 'barbero' está lista</strong>";
} else {
    echo "❌ Error al actualizar: " . $conn->error . "<br>";
}

echo "<hr>";
echo "<a href='login.php' style='padding:10px;background:#C5A059;color:#000;text-decoration:none;border-radius:4px;'>";
echo "→ Ir a login.php - Intenta con admin@barberiapro.com / 0000</a><br>";
?>

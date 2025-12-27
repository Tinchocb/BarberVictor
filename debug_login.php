<?php
/**
 * DEBUG LOGIN - SIMULA EXACTAMENTE PASO A PASO
 * ============================================
 */

require_once __DIR__ . "/config/branding.php";
require_once __DIR__ . "/config/conexion.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🐛 DEBUG LOGIN</h1>";
echo "<hr>";

// Simular datos del login
$email_test = "admin@barberiapro.com";
$password_test = "0000";

echo "<h2>1. Datos de prueba:</h2>";
echo "Email: <code>$email_test</code><br>";
echo "Password: <code>$password_test</code><br><br>";

// 2. Verificar conexión
echo "<h2>2. Conexión a BD:</h2>";
if (!$conn) {
    echo "❌ SIN CONEXIÓN A BD<br>";
    exit;
} else {
    echo "✅ Conexión OK<br><br>";
}

// 3. Buscar usuario por email
echo "<h2>3. Buscando usuario por email:</h2>";
$sql = "SELECT id, usuario, password FROM usuarios WHERE email = ? LIMIT 1";
echo "SQL: <code>$sql</code><br>";
echo "Parámetro: <code>$email_test</code><br><br>";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "❌ ERROR en prepare: " . $conn->error . "<br>";
    exit;
} else {
    echo "✅ Prepare OK<br>";
}

$stmt->bind_param("s", $email_test);
echo "✅ Bind param OK<br>";

if (!$stmt->execute()) {
    echo "❌ ERROR en execute: " . $stmt->error . "<br>";
    exit;
} else {
    echo "✅ Execute OK<br>";
}

$resultado = $stmt->get_result();
echo "✅ Get result OK<br>";

if (!$resultado) {
    echo "❌ ERROR getting result<br>";
    exit;
}

echo "Rows found: " . $resultado->num_rows . "<br><br>";

// 4. Verificar resultado
echo "<h2>4. Datos del usuario encontrado:</h2>";
if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();
    echo "✅ Usuario encontrado<br>";
    echo "ID: " . htmlspecialchars($usuario['id']) . "<br>";
    echo "Usuario: " . htmlspecialchars($usuario['usuario']) . "<br>";
    echo "Password hash: <code>" . htmlspecialchars($usuario['password']) . "</code><br><br>";
    
    // 5. Verificar contraseña
    echo "<h2>5. Verificación de contraseña:</h2>";
    echo "Password ingresado: <code>$password_test</code><br>";
    echo "Función: <code>password_verify('$password_test', hash)</code><br><br>";
    
    $verify_result = password_verify($password_test, $usuario['password']);
    
    if ($verify_result === true) {
        echo "✅ <strong>PASSWORD CORRECTO - LOGIN DEBERÍA FUNCIONAR</strong><br>";
    } elseif ($verify_result === false) {
        echo "❌ <strong>PASSWORD INCORRECTO</strong><br>";
        echo "El hash guardado no coincide con la contraseña '0000'<br>";
        
        // Intentar regenerar hash
        echo "<br><h2>6. Regenerando contraseña:</h2>";
        $new_hash = password_hash($password_test, PASSWORD_DEFAULT);
        echo "Nuevo hash: <code>$new_hash</code><br>";
        
        $update = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $update->bind_param("si", $new_hash, $usuario['id']);
        if ($update->execute()) {
            echo "✅ Contraseña actualizada<br>";
            echo "Intenta login nuevamente en 2 segundos...<br>";
            echo "<script>setTimeout(() => { window.location.href='login.php'; }, 2000);</script>";
        } else {
            echo "❌ Error updating: " . $update->error . "<br>";
        }
    } else {
        echo "❌ ERROR desconocido en password_verify<br>";
    }
} else {
    echo "❌ <strong>USUARIO NO ENCONTRADO</strong> con email: $email_test<br>";
    echo "Usuarios en BD:<br>";
    $all_users = $conn->query("SELECT id, usuario, email FROM usuarios");
    if ($all_users) {
        while ($u = $all_users->fetch_assoc()) {
            echo "- ID: " . $u['id'] . ", Usuario: " . $u['usuario'] . ", Email: " . $u['email'] . "<br>";
        }
    }
}

$stmt->close();
echo "<hr>";
echo "<a href='login.php'>← Volver a login.php</a>";
?>

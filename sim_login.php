<?php
/**
 * SIMULAR LOGIN SIN REDIRECT
 * ==========================
 * Intenta hacer login como lo hace login.php pero sin exit/redirect
 */

session_start();

require_once __DIR__ . "/config/branding.php";
require_once __DIR__ . "/config/conexion.php";

echo "<h1>🔐 SIMULANDO LOGIN</h1>";
echo "<hr>";

$email = "admin@barberiapro.com";
$password = "0000";

echo "Email: <code>$email</code><br>";
echo "Password: <code>$password</code><br><br>";

// Validar CSRF simulado (no es post real)
echo "1. ✅ Saltando validación CSRF (es test)<br>";

// Validar datos
if (empty($email) || empty($password)) {
    echo "❌ Campos vacíos<br>";
    exit;
} else {
    echo "2. ✅ Campos no vacíos<br>";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Email inválido<br>";
    exit;
} else {
    echo "3. ✅ Email válido<br>";
}

// Buscar usuario
if (!$conn) {
    echo "❌ Sin conexión BD<br>";
    exit;
}

$sql = "SELECT id, usuario, password FROM usuarios WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "❌ Prepare error: " . $conn->error . "<br>";
    exit;
} else {
    echo "4. ✅ Prepare OK<br>";
}

$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if (!$resultado) {
    echo "❌ Get result error<br>";
    exit;
} else {
    echo "5. ✅ Get result OK<br>";
}

echo "Rows: " . $resultado->num_rows . "<br>";

if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();
    echo "6. ✅ Usuario encontrado: " . htmlspecialchars($usuario['usuario']) . "<br>";
    
    // Verificar password
    if (password_verify($password, $usuario['password'])) {
        echo "7. ✅ Password correcto<br>";
        
        // Crear sesión
        session_regenerate_id(true);
        $_SESSION['acceso_autorizado'] = true;
        $_SESSION['usuario_id'] = intval($usuario['id']);
        $_SESSION['usuario_nombre'] = htmlspecialchars($usuario['usuario']);
        $_SESSION['usuario_rol'] = 'admin';
        $_SESSION['login_time'] = time();
        
        echo "8. ✅ Sesión creada<br>";
        echo "SESSION['acceso_autorizado'] = " . var_export($_SESSION['acceso_autorizado'], true) . "<br>";
        echo "SESSION['usuario_id'] = " . $_SESSION['usuario_id'] . "<br>";
        echo "SESSION['usuario_nombre'] = " . $_SESSION['usuario_nombre'] . "<br>";
        
        echo "<br><strong>✅ TODO CORRECTO - LOGIN DEBERÍA FUNCIONAR</strong><br>";
        echo "Ahora intenta entrar a login.php e ingresa:<br>";
        echo "- Email: <code>admin@barberiapro.com</code><br>";
        echo "- Password: <code>0000</code><br>";
        echo "<a href='login.php' style='display:block;margin-top:20px;padding:10px;background:#C5A059;color:#000;text-decoration:none;border-radius:4px;width:200px;text-align:center;'>IR A LOGIN.PHP</a>";
    } else {
        echo "❌ Password incorrecto<br>";
    }
} else {
    echo "❌ Usuario no encontrado<br>";
}

$stmt->close();
?>

<?php
/**
 * DEBUG POST - CAPTURA LO QUE LLEGA EN EL FORMULARIO
 * ===================================================
 */

session_start();
require_once __DIR__ . "/config/branding.php";

echo "<h1>🔍 CAPTURA DE POST</h1>";
echo "<hr>";

// Si es POST, mostrar lo que llegó
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>📨 POST RECIBIDO:</h2>";
    echo "<pre>";
    var_dump($_POST);
    echo "</pre>";
    
    echo "<h2>Validación CSRF:</h2>";
    if (isset($_POST['csrf_token'])) {
        $token_recibido = $_POST['csrf_token'];
        $token_sesion = $_SESSION['csrf_token'] ?? 'NO EXISTE';
        
        echo "Token en POST: <code>$token_recibido</code><br>";
        echo "Token en SESSION: <code>$token_sesion</code><br>";
        
        if (verificarTokenCSRF($token_recibido)) {
            echo "✅ CSRF válido<br>";
        } else {
            echo "❌ CSRF inválido<br>";
        }
    } else {
        echo "❌ No hay CSRF token en POST<br>";
    }
    
    echo "<h2>Datos del formulario:</h2>";
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    echo "Email: <code>$email</code><br>";
    echo "Password: <code>$password</code><br>";
    
    if (empty($email) || empty($password)) {
        echo "❌ Campos vacíos<br>";
    } else {
        echo "✅ Campos presentes<br>";
    }
    
    exit;
}

// Si es GET, mostrar el formulario
$csrf_token = generarTokenCSRF();
echo "<h2>Formulario de prueba:</h2>";
echo "<form method='POST' action=''>
  <input type='hidden' name='csrf_token' value='" . htmlspecialchars($csrf_token) . "'>
  
  <label>Email:</label><br>
  <input type='email' name='email' value='admin@barberiapro.com' required><br><br>
  
  <label>Contraseña:</label><br>
  <input type='password' name='password' value='0000' required><br><br>
  
  <button type='submit'>ENVIAR</button>
</form>";

echo "<hr>";
echo "<p>Completa el formulario y presiona ENVIAR para ver qué llega en el POST</p>";
?>

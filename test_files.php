<?php
// Test file validation script
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
$_SESSION['acceso_autorizado'] = true;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

require_once "config/conexion.php";

echo "<h2>Testing configuracion.php syntax</h2>";
try {
    ob_start();
    include "configuracion.php";
    ob_end_clean();
    echo "<p style='color:green;'>✓ configuracion.php loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Testing pedidos.php syntax</h2>";
try {
    ob_start();
    include "pedidos.php";
    ob_end_clean();
    echo "<p style='color:green;'>✓ pedidos.php loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Database tables verification</h2>";
$tables = $conn->query("SHOW TABLES");
if ($tables) {
    echo "<p>Tables in database:</p><ul>";
    while ($row = $tables->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
}
?>

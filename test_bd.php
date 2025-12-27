<?php
/**
 * TEST BD - DIAGNÓSTICO
 * ====================
 * Verifica conexión, tablas y usuario
 */

require_once __DIR__ . "/config/conexion.php";

echo "<h1>🔍 DIAGNÓSTICO DE BASE DE DATOS</h1>";
echo "<hr>";

// 1. Verificar conexión
if ($conn) {
    echo "✅ <strong>Conexión a BD correcta</strong><br>";
    echo "Base de datos: " . DB_NAME . "<br><br>";
} else {
    echo "❌ <strong>ERROR: No hay conexión a BD</strong><br>";
    exit;
}

// 2. Verificar tabla usuarios
echo "<h2>Tabla: usuarios</h2>";
$check_table = $conn->query("SHOW TABLES LIKE 'usuarios'");
if ($check_table && $check_table->num_rows > 0) {
    echo "✅ Tabla 'usuarios' existe<br><br>";
    
    // 3. Mostrar estructura
    echo "<h3>Estructura de tabla usuarios:</h3>";
    $columns = $conn->query("DESCRIBE usuarios");
    if ($columns) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($col = $columns->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // 4. Verificar usuario barbero
    echo "<h3>Usuarios en tabla:</h3>";
    $users = $conn->query("SELECT id, usuario, email FROM usuarios");
    if ($users && $users->num_rows > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Email</th></tr>";
        while ($user = $users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['usuario']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Verificar si barbero existe
        if ($users->num_rows > 0) {
            $users = $conn->query("SELECT id FROM usuarios WHERE usuario = 'barbero'");
            if ($users && $users->num_rows > 0) {
                echo "✅ Usuario 'barbero' existe en tabla<br>";
            } else {
                echo "❌ Usuario 'barbero' NO existe en tabla<br>";
            }
        }
    } else {
        echo "⚠️ Tabla usuarios está vacía<br>";
    }
    
} else {
    echo "❌ Tabla 'usuarios' NO existe<br>";
    echo "<strong>Debes crear la tabla manualmente o importar la BD desde database/bd_barberia.sql</strong><br>";
}

echo "<hr>";
echo "<a href='reset_admin.php'>→ Ir a reset_admin.php para crear/actualizar credenciales</a><br>";
echo "<a href='login.php'>→ Ir a login.php para intentar login</a><br>";
?>

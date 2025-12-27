<?php
require __DIR__ . '/config/conexion.php';
if ($conn) {
    echo "CONEXION_OK\n";
} else {
    echo "CONEXION_FALLIDA\n";
}

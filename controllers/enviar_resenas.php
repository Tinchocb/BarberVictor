<?php
/**
 * SCRIPT CRON - SOLICITUD DE RESEÑAS
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../includes/PHPMailer/Exception.php';
require __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require __DIR__ . '/../includes/PHPMailer/SMTP.php';
require_once __DIR__ . "/../config/conexion.php";

// Link por defecto
$link_maps = 'https://g.page/r/tu-negocio/review';
if ($conn->query("SHOW TABLES LIKE 'configuracion_emails'")->num_rows > 0) {
    $config = $conn->query("SELECT * FROM configuracion_emails LIMIT 1")->fetch_assoc();
    if($config) $link_maps = $config['link_google_maps'];
}

echo "🔍 Buscando turnos completados hace poco...\n";

// Buscar turnos completados hace entre 2 y 3 horas
$desde = date('Y-m-d H:i:s', strtotime('-3 hours'));
$hasta = date('Y-m-d H:i:s', strtotime('-2 hours'));

$sql = "SELECT r.*, b.nombre as barbero_nombre 
        FROM reservas r 
        JOIN barberos b ON r.id_barbero = b.id_barbero
        WHERE r.fecha_hora BETWEEN '$desde' AND '$hasta'
        AND r.estado = 'completada'
        AND r.resena_enviada = 0
        AND r.email IS NOT NULL AND r.email != ''";

$result = $conn->query($sql);
$enviados = 0;

if ($result && $result->num_rows > 0) {
    while($turno = $result->fetch_assoc()) {
        $mail = new PHPMailer(true);
        try {
            // --- CONFIGURACIÓN DEFAULT (PLACEHOLDERS) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'usuario_default@gmail.com'; // <--- CAMBIAR
            $mail->Password   = 'password_default';          // <--- CAMBIAR
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('turnos@victorbarberclub.com', 'Victor Barber Club');
            $mail->addAddress($turno['email'], $turno['cliente']);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = '⭐ ¿Cómo estuvo tu corte?';
            
            $mail->Body = "
            <div style='background:#121212; padding:30px; color:#ddd; font-family:sans-serif; text-align:center;'>
                <h2 style='color:#C5A059;'>¡Gracias por venir!</h2>
                <p>Esperamos que hayas disfrutado tu experiencia con <strong>{$turno['barbero_nombre']}</strong>.</p>
                <div style='margin:30px 0;'>
                    <a href='$link_maps' style='background:#C5A059; color:#000; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:bold;'>DEJAR 5 ESTRELLAS ⭐</a>
                </div>
            </div>";

            $mail->send();
            
            // Marcar como enviado
            $conn->query("UPDATE reservas SET resena_enviada = 1 WHERE id = {$turno['id']}");
            $enviados++;
            echo "✅ Solicitud enviada a {$turno['cliente']}\n";
            
        } catch (Exception $e) {
            echo "❌ Error mail: {$mail->ErrorInfo}\n";
        }
    }
} else {
    echo "No hay reseñas para pedir.\n";
}

echo "Total enviados: $enviados\n";
$conn->close();
?>
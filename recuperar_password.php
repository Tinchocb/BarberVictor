<?php
/**
 * RECUPERACIÓN DE CONTRASEÑA - VERSIÓN SEGURA
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/includes/PHPMailer/Exception.php';
require __DIR__ . '/includes/PHPMailer/PHPMailer.php';
require __DIR__ . '/includes/PHPMailer/SMTP.php';
require_once __DIR__ . '/config/conexion.php'; // Aquí están las constantes SMTP

$mensaje = "";
$step = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_reset'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Evitamos User Enumeration: No mostramos si el email existe o no explícitamente en el UI
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));
        
        $stmt_upd = $conn->prepare("UPDATE usuarios SET token_recovery=?, token_expira=? WHERE email=?");
        $stmt_upd->bind_param("sss", $token, $expira, $email);
        $stmt_upd->execute();
        
        $mail = new PHPMailer(true);
        try {
            // USAMOS CONSTANTES (Definidas en conexion.php en la respuesta anterior)
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_USER, 'Seguridad - Victor Barber Club');
            $mail->addAddress($email);

            // SEGURIDAD: Usar APP_URL definida en config para evitar Host Header Injection
            // Si APP_URL no está definida, define esto en conexion.php: define('APP_URL', 'http://tusitio.com');
            $link = APP_URL . "/recuperar_password.php?token=$token";

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = '🔑 Restablecer Clave';
            $mail->Body    = "
            <div style='background:#121212; padding:30px; color:#fff; text-align:center;'>
                <h2>Recuperación</h2>
                <p>Clic para cambiar tu clave (válido por 1 hora):</p>
                <a href='$link' style='color:#C5A059;'>CLIC AQUÍ PARA RESTABLECER</a>
            </div>";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
        }
    }
    // Siempre avanzamos al paso 2 para no revelar si el email existe
    $step = 2;
    $stmt->close();
}

// ... (El resto de la lógica de pasos 2 y 3 es aceptable, pero asegúrate de usar prepared statements como ya tenías)
// Mantengo el HTML original abreviado por espacio
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Recuperar</title><link rel="stylesheet" href="assets/css/global.css"><link rel="stylesheet" href="assets/css/stylesLogin.css"></head>
<body class="login-body">
    <div class="login-container" style="max-width:400px; margin:auto; margin-top:50px;">
        <h2 style="text-align:center; color:#C5A059">RECUPERAR</h2>
        <?php if($mensaje): ?><div class="error-msg"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
        
        <?php if($step == 1): ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email admin" required style="width:100%; padding:10px; margin:10px 0;">
                <button type="submit" name="solicitar_reset" class="btn-login">ENVIAR ENLACE</button>
            </form>
        <?php elseif($step == 2): ?>
            <p style="color:white; text-align:center;">Si el correo coincide con un administrador, recibirás las instrucciones en breve.</p>
            <a href="login.php" style="display:block; text-align:center; color:#C5A059; margin-top:20px;">Volver</a>
        <?php elseif($step == 3): ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                <input type="password" name="password" placeholder="Nueva Clave (min 8 chars)" required minlength="8" style="width:100%; padding:10px; margin:10px 0;">
                <button type="submit" name="guardar_clave" class="btn-login">GUARDAR NUEVA CLAVE</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
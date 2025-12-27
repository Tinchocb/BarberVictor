<?php
/**
 * GUARDAR RESERVA - VERSIÓN SEGURA Y ROBUSTA
 * ==========================================
 * Implementa validaciones, CSRF protection y prepared statements
 */

// Configuración segura
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Definir rutas absolutas
$base_dir = dirname(__DIR__);

// Cargar configuración y librerías
require_once "$base_dir/config/branding.php";
require_once "$base_dir/config/conexion.php";

// Cargar PHPMailer si existe
if (file_exists("$base_dir/includes/PHPMailer/Exception.php")) {
    require "$base_dir/includes/PHPMailer/Exception.php";
    require "$base_dir/includes/PHPMailer/PHPMailer.php";
    require "$base_dir/includes/PHPMailer/SMTP.php";
}

// Headers de seguridad
// Detectar si la petición espera JSON (AJAX) o fue un submit de formulario normal
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
$is_xhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$expectsJson = (stripos($accept, 'application/json') !== false) || $is_xhr;
if ($expectsJson) {
    header('Content-Type: application/json; charset=utf-8');
}
header('X-Content-Type-Options: nosniff');

// Variables de respuesta
$respuesta = ['exito' => false, 'mensaje' => 'Error desconocido', 'token' => null];

// Validar método HTTP
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    $respuesta['mensaje'] = 'Método no permitido';
    die(json_encode($respuesta));
}

try {
    // PASO 1: Validar token CSRF
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarTokenCSRF($csrf_token)) {
        throw new Exception("Token de seguridad inválido o expirado.");
    }
    
    // PASO 2: Validar conexión a BD
    if (!$conn) {
        throw new Exception("Error de conexión a base de datos.");
    }

    // PASO 3: Sanitizar y validar entrada
    $id_barbero = intval($_POST['id_barbero'] ?? 0);
    $servicio = trim($_POST['servicio'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $cliente = trim($_POST['cliente'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $dni = preg_replace('/\D/', '', $_POST['id_cliente'] ?? '');
    $telefono = preg_replace('/\D/', '', $_POST['telefono'] ?? '');
    $pago = trim($_POST['pago'] ?? 'Efectivo');

    // PASO 4: Validaciones estrictas
    if ($id_barbero <= 0) {
        throw new Exception("Seleccione un profesional válido.");
    }
    if (empty($servicio) || strlen($servicio) > 100) {
        throw new Exception("Servicio inválido.");
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        throw new Exception("Formato de fecha inválido.");
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
        throw new Exception("Formato de hora inválido.");
    }
    if (empty($cliente) || strlen($cliente) < 3 || strlen($cliente) > 100) {
        throw new Exception("Nombre inválido (3-100 caracteres).");
    }
    if (!$email) {
        throw new Exception("Email inválido.");
    }
    if (strlen($dni) < 5 || strlen($dni) > 12) {
        throw new Exception("DNI inválido.");
    }
    if (strlen($telefono) < 8 || strlen($telefono) > 15) {
        throw new Exception("Teléfono inválido.");
    }
    
    // Validar que la fecha sea futura
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fecha_obj || $fecha_obj <= new DateTime()) {
        throw new Exception("Seleccione una fecha futura.");
    }

    $fecha_hora = "$fecha $hora:00";
    
    // PASO 5: Iniciar transacción
    $conn->begin_transaction();

    // PASO 6: Verificar disponibilidad (anti-doble reserva)
    $stmt_check = $conn->prepare("SELECT id FROM reservas WHERE id_barbero = ? AND fecha_hora = ? AND estado = 'activa' LIMIT 1");
    if (!$stmt_check) {
        throw new Exception("Error en preparación: " . $conn->error);
    }
    $stmt_check->bind_param("is", $id_barbero, $fecha_hora);
    if (!$stmt_check->execute()) {
        throw new Exception("Error en validación: " . $stmt_check->error);
    }
    
    if ($stmt_check->get_result()->num_rows > 0) {
        throw new Exception("El turno seleccionado ya no está disponible.");
    }
    $stmt_check->close();

    // PASO 7: Generar tokens seguros
    $token_cancelacion = bin2hex(random_bytes(32));
    $token_resena = bin2hex(random_bytes(32));

    // PASO 8: Insertar reserva
    $sql_insert = "INSERT INTO reservas (fecha_hora, cliente, id_barbero, servicio, pago, id_cliente, telefono, email, token_cancelacion, token_resena, estado) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activa')";
    
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        throw new Exception("Error en preparación: " . $conn->error);
    }
    
    $stmt_insert->bind_param("ssisssssss", $fecha_hora, $cliente, $id_barbero, $servicio, $pago, $dni, $telefono, $email, $token_cancelacion, $token_resena);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("Error al insertar: " . $stmt_insert->error);
    }
    
    $id_reserva = $conn->insert_id;
    $stmt_insert->close();

    // PASO 9: Enviar email de confirmación (opcional, sin bloquear)
    if (class_exists('PHPMailer\PHPMailer\PHPMailer') && !empty($email)) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER') ?: 'tu-email@gmail.com';
            $mail->Password = getenv('SMTP_PASS') ?: 'tu-clave';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom(getenv('SMTP_FROM') ?: CONTACT_EMAIL, BRAND_NAME);
            $mail->addAddress($email, htmlspecialchars($cliente));
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = '✅ Turno Confirmado - ' . BRAND_NAME;
            $mail->Body = "
                <h2>¡Hola " . htmlspecialchars($cliente) . "!</h2>
                <p>Tu reserva en <strong>" . BRAND_NAME . "</strong> ha sido confirmada.</p>
                <hr>
                <p><strong>Servicio:</strong> " . htmlspecialchars($servicio) . "</p>
                <p><strong>Fecha:</strong> " . htmlspecialchars($fecha) . "</p>
                <p><strong>Hora:</strong> " . htmlspecialchars($hora) . "</p>
                <p><strong>Método de pago:</strong> " . htmlspecialchars($pago) . "</p>
                <hr>
                <p><a href='" . getenv('APP_URL') . "exito.php?t=" . urlencode($token_cancelacion) . "'>Ver detalles de tu reserva</a></p>
            ";
            $mail->send();
        } catch (Exception $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Email Error: " . $e->getMessage());
        }
    }

    // PASO 10: Confirmar transacción
    $conn->commit();
    
    // Respuesta exitosa
    $respuesta['exito'] = true;
    $respuesta['mensaje'] = 'Reserva confirmada exitosamente.';
    $respuesta['token'] = $token_cancelacion;
    $respuesta['id_reserva'] = $id_reserva;
    
    if ($expectsJson) {
        http_response_code(200);
        die(json_encode($respuesta));
    } else {
        // Petición desde formulario normal -> redirigir a página de éxito
        $url = (getenv('APP_URL') ?: '') . 'exito.php?t=' . urlencode($token_cancelacion);
        header('Location: ' . $url);
        exit;
    }

} catch (Exception $e) {
    // Rollback en caso de error
    if ($conn) {
        $conn->rollback();
    }
    
    error_log("[" . date('Y-m-d H:i:s') . "] Reserva Error: " . $e->getMessage());
    
    // En caso de error: si es petición AJAX devolvemos JSON, si no redirigimos con mensaje
    if ($expectsJson) {
        $respuesta['mensaje'] = $e->getMessage();
        http_response_code(400);
        die(json_encode($respuesta));
    } else {
        $msg = urlencode($e->getMessage());
        $back = (getenv('APP_URL') ?: '') . 'reserva.php?error=' . $msg;
        header('Location: ' . $back);
        exit;
    }
}
?>
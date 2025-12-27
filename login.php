<?php
/**
 * LOGIN - PANEL STAFF
 * ==================
 * Autenticación segura con validaciones robustas
 * CORREGIDO: Ajustado a la estructura real de la base de datos (tabla usuarios)
 */

require_once __DIR__ . "/config/branding.php";
require_once __DIR__ . "/config/conexion.php";

$error = "";

// Redirigir si ya está autenticado
if (isset($_SESSION['acceso_autorizado']) && $_SESSION['acceso_autorizado'] === true) {
    header("Location: pedidos.php");
    exit();
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$conn) {
        $error = "Error de conexión a la base de datos. Intentá más tarde.";
    } else {
        // Validar CSRF
        if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
            $error = "Token de seguridad inválido.";
        } else {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Validaciones
            if (empty($email) || empty($password)) {
                $error = "Por favor completá todos los campos.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Email inválido.";
            } else {
                // CORRECCIÓN PRINCIPAL:
                // Seleccionamos 'usuario' en vez de 'nombre' y quitamos 'rol' que no existe en la BD.
                $sql = "SELECT id, usuario, password FROM usuarios WHERE email = ? LIMIT 1";
                
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $resultado = $stmt->get_result();

                    if ($resultado && $resultado->num_rows === 1) {
                        $usuario = $resultado->fetch_assoc();
                        
                        // Verificar contraseña con hash seguro
                        if (password_verify($password, $usuario['password'])) {
                            // Regenerar session ID contra session fixation
                            session_regenerate_id(true);
                            $_SESSION['acceso_autorizado'] = true;
                            $_SESSION['usuario_id'] = intval($usuario['id']);
                            
                            // CORRECCIÓN: Usamos la columna 'usuario' que sí existe
                            $_SESSION['usuario_nombre'] = htmlspecialchars($usuario['usuario']);
                            
                            // CORRECCIÓN: Asignamos 'admin' por defecto ya que no hay columna de roles
                            $_SESSION['usuario_rol'] = 'admin'; 
                            
                            $_SESSION['login_time'] = time();

                            // Log de acceso
                            error_log("[" . date('Y-m-d H:i:s') . "] Staff login: " . htmlspecialchars($email));

                            // Redirigir a dashboard
                            header("Location: index.html");
                            exit;
                        } else {
                            $error = "Credenciales inválidas.";
                        }
                    } else {
                        $error = "Usuario no encontrado.";
                    }
                    $stmt->close();
                } else {
                    // Si falla el prepare, mostramos error técnico solo en logs, mensaje genérico al usuario
                    error_log("Error en consulta SQL Login: " . $conn->error);
                    $error = "Error interno del servidor. Contacte al soporte.";
                }
            }
        }
    }
}

$csrf_token = generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | <?= BRAND_NAME ?> - Staff</title>
    <link rel="stylesheet" href="assets/css/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/animations.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-animated">
    <div class="login-wrapper">
        <div class="card-glass animate-entry" style="width: 100%; max-width: 450px; padding: 50px 40px;">
            <div class="text-center mb-4">
                <i class="fas fa-cut" style="font-size: 3.5rem; color: var(--accent-gold); margin-bottom: 15px; text-shadow: var(--glow-gold); animation: float 3s ease-in-out infinite;"></i>
                <h2 style="color: var(--accent-gold); font-size: 1.8rem; margin: 10px 0; text-shadow: var(--glow-gold);"><?= BRAND_NAME ?></h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">PANEL DE CONTROL</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg animate-fadeIn">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div class="input-group">
                    <input 
                        type="email" 
                        name="email" 
                        placeholder="tu-email@barberia.com" 
                        required 
                        autofocus
                        pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                    >
                    <i class="fas fa-user"></i>
                </div>

                <div class="input-group">
                    <input 
                        type="password" 
                        name="password" 
                        placeholder="Contraseña" 
                        required
                    >
                    <i class="fas fa-lock"></i>
                </div>

                <button type="submit" class="btn-primary-luxury btn-full hover-lift">
                    <i class="fas fa-sign-in-alt"></i> INGRESAR
                </button>
            </form>

            <div class="text-center mt-3" style="border-top: 1px solid rgba(197, 160, 89, 0.1); padding-top: 20px;">
                <a href="recuperar_password.php" style="font-size: 0.9rem; margin-bottom: 10px; display: block;">
                    <i class="fas fa-key"></i> ¿Olvidaste tu contraseña? Restablecer
                </a>
                <a href="index.html" style="font-size: 0.9rem;">
                    <i class="fas fa-arrow-left"></i> Volver al sitio
                </a>
            </div>
        </div>
    </div>
</body>
</html>
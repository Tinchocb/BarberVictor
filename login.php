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
            $input_user = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Validaciones
            if (empty($input_user) || empty($password)) {
                $error = "Por favor completá todos los campos.";
            } else {
                // CORRECCIÓN PRINCIPAL: Permitir login por email O usuario
                $sql = "SELECT id, usuario, password FROM usuarios WHERE email = ? OR usuario = ? LIMIT 1";
                
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("ss", $input_user, $input_user);
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
                            $_SESSION['usuario_nombre'] = htmlspecialchars($usuario['usuario']);
                            $_SESSION['usuario_rol'] = 'admin';  // Default rol
                            $_SESSION['login_time'] = time();

                            // Log de acceso
                            error_log("[" . date('Y-m-d H:i:s') . "] Staff login success: " . htmlspecialchars($input_user));

                            // Redirigir a dashboard
                            header("Location: index.php");
                            exit;
                        } else {
                            $error = "Credenciales inválidas.";
                        }
                    } else {
                        $error = "Usuario no encontrado.";
                    }
                    $stmt->close();
                } else {
                    error_log("Error en consulta SQL Login: " . $conn->error);
                    $error = "Error interno del servidor.";
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
    <link rel="stylesheet" href="assets/css/animations-premium.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
</head>
<body class="login-wrapper">
    <div class="login-container fade-in-up">
        <div class="text-center mb-4">
            <div class="text-gold" style="font-size: 3rem; margin-bottom: 20px;">
                <i class="fas fa-user-shield"></i>
            </div>
            <h2 class="text-white" style="font-family: 'Oswald', sans-serif; letter-spacing: 2px;">STAFF LOGIN</h2>
            <p class="text-muted">Acceso exclusivo para personal autorizado</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="card-premium" style="border-color: var(--accent-error); padding: 15px; margin-bottom: 20px; background: rgba(239, 68, 68, 0.1);">
                <i class="fas fa-exclamation-circle" style="color: var(--accent-error);"></i> 
                <span class="text-white" style="margin-left: 10px;"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="mb-3">
                <label class="form-label text-gold">Usuario o Email</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="email" class="form-control" placeholder="Ingresá tu usuario o email" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-gold">Contraseña</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full ripple-effect">
                INGRESAR <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" class="text-muted" style="text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-arrow-left"></i> Volver al sitio
            </a>
            <br><br>
            <a href="reset_admin.php" class="text-muted" style="font-size: 0.8rem; opacity: 0.5;">¿Olvidaste tu contraseña?</a>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="assets/js/animations.js?v=<?= time(); ?>"></script>
</body>
                </a>
                <a href="index.php" style="font-size: 0.9rem;">
                    <i class="fas fa-arrow-left"></i> Volver al sitio
                </a>
            </div>
        </div>
    </div>
    
    <!-- PREMIUM ANIMATION SCRIPTS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="assets/js/gsap-init.js?v=<?= time(); ?>"></script>
    <script src="assets/js/micro-interactions.js?v=<?= time(); ?>"></script>
    <script>
        // Loading spinner on form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> INGRESANDO...';
            btn.disabled = true;
            btn.style.opacity = '0.8';
        });
        
        // Animate form entrance
        if (typeof gsap !== 'undefined') {
            gsap.from('.card-glass', {
                opacity: 0,
                y: 30,
                duration: 0.6,
                ease: 'power2.out'
            });
            
            gsap.from('.input-group', {
                opacity: 0,
                y: 15,
                duration: 0.4,
                stagger: 0.1,
                delay: 0.3,
                ease: 'power2.out'
            });
        }
    </script>
</body>
</html>
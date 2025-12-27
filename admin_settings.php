<?php
/**
 * ADMIN SETTINGS - Gestión de Credenciales
 * =========================================
 * Permite al admin cambiar email y contraseña de forma segura
 */

require_once __DIR__ . "/config/branding.php";
require_once __DIR__ . "/config/conexion.php";

// Verificar acceso admin
if (!isset($_SESSION['acceso_autorizado']) || $_SESSION['acceso_autorizado'] !== true) {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// Procesar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
        $mensaje = "Token de seguridad inválido.";
        $tipo_mensaje = "error";
    } else {
        $accion = $_POST['accion'] ?? '';
        $password_actual = $_POST['password_actual'] ?? '';
        $usuario_id = $_SESSION['usuario_id'];
        
        // Verificar contraseña actual
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
        
        if (!$usuario || !password_verify($password_actual, $usuario['password'])) {
            $mensaje = "Contraseña actual incorrecta.";
            $tipo_mensaje = "error";
        } else {
            if ($accion === 'cambiar_email') {
                $nuevo_email = filter_var(trim($_POST['nuevo_email'] ?? ''), FILTER_VALIDATE_EMAIL);
                
                if (!$nuevo_email) {
                    $mensaje = "Email inválido.";
                    $tipo_mensaje = "error";
                } else {
                    $stmt = $conn->prepare("UPDATE usuarios SET email = ? WHERE id = ?");
                    $stmt->bind_param("si", $nuevo_email, $usuario_id);
                    
                    if ($stmt->execute()) {
                        $mensaje = "Email actualizado correctamente.";
                        $tipo_mensaje = "success";
                    } else {
                        $mensaje = "Error al actualizar email.";
                        $tipo_mensaje = "error";
                    }
                    $stmt->close();
                }
            } elseif ($accion === 'cambiar_password') {
                $nuevo_password = $_POST['nuevo_password'] ?? '';
                $confirmar_password = $_POST['confirmar_password'] ?? '';
                
                if (strlen($nuevo_password) < 8) {
                    $mensaje = "La contraseña debe tener al menos 8 caracteres.";
                    $tipo_mensaje = "error";
                } elseif ($nuevo_password !== $confirmar_password) {
                    $mensaje = "Las contraseñas no coinciden.";
                    $tipo_mensaje = "error";
                } else {
                    // Hash seguro con Argon2i
                    $hash = password_hash($nuevo_password, PASSWORD_ARGON2ID);
                    
                    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $hash, $usuario_id);
                    
                    if ($stmt->execute()) {
                        $mensaje = "Contraseña actualizada correctamente.";
                        $tipo_mensaje = "success";
                    } else {
                        $mensaje = "Error al actualizar contraseña.";
                        $tipo_mensaje = "error";
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// Obtener email actual
$email_actual = "";
$stmt = $conn->prepare("SELECT email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $email_actual = $row['email'];
}
$stmt->close();

$csrf_token = generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración | <?= BRAND_NAME ?></title>
    <link rel="stylesheet" href="assets/css/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/animations.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/animations-premium.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-container {
            max-width: 600px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }
        
        .settings-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .settings-header h1 {
            color: var(--accent-gold);
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .settings-card {
            background: rgba(20, 20, 20, 0.95);
            border: 1px solid rgba(197, 160, 89, 0.2);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 25px;
        }
        
        .settings-card h3 {
            color: var(--accent-gold);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(30, 30, 30, 0.9);
            border: 2px solid rgba(197, 160, 89, 0.2);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--accent-gold);
            box-shadow: 0 0 15px rgba(197, 160, 89, 0.15);
        }
        
        .btn-save {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent-gold), #E8C547);
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(197, 160, 89, 0.3);
        }
        
        .mensaje {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .mensaje.success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid #10b981;
            color: #10b981;
        }
        
        .mensaje.error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #ef4444;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--accent-gold);
            text-decoration: none;
            margin-bottom: 30px;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .back-link:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <a href="pedidos.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
        
        <div class="settings-header">
            <h1><i class="fas fa-cog"></i> Configuración</h1>
            <p style="color: var(--text-muted);">Gestiona tus credenciales de acceso</p>
        </div>
        
        <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipo_mensaje ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
        <?php endif; ?>
        
        <!-- Cambiar Email -->
        <div class="settings-card">
            <h3><i class="fas fa-envelope"></i> Cambiar Email</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="accion" value="cambiar_email">
                
                <div class="form-group">
    <label>Email Actual</label>
    <span id="email-display"><?php echo htmlspecialchars($email_actual) ?></span>
    <button type="button" id="copy-email-btn" class="btn-save btn-icon"><i class="fas fa-copy"></i> Copiar Email</button>
</div>
<div class="form-group">
    <label>Nuevo Email</label>
    <input type="email" name="nuevo_email" required placeholder="nuevo@email.com">
</div>
<div class="form-group">
    <label>Contraseña Actual (verificación)</label>
    <input type="password" name="password_actual" required placeholder="Tu contraseña actual">
</div>
                
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Guardar Email
                </button>
            </form>
        </div>
        
        <!-- Cambiar Contraseña -->
        <div class="settings-card">
            <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="accion" value="cambiar_password">
                
                <div class="form-group">
                    <label>Contraseña Actual</label>
                    <input type="password" name="password_actual" required placeholder="Tu contraseña actual">
                </div>
                
                <div class="form-group">
                    <label>Nueva Contraseña</label>
                    <input type="password" name="nuevo_password" required placeholder="Mínimo 8 caracteres" minlength="8" id="new-password">
                <div class="strength-meter" id="strength-meter"><div class="fill"></div></div>
                </div>
                
                <div class="form-group">
                    <label>Confirmar Nueva Contraseña</label>
                    <input type="password" name="confirmar_password" required placeholder="Repetir contraseña">
                </div>
                
                <button type="submit" class="btn-save">
                    <i class="fas fa-key"></i> Cambiar Contraseña
                </button>
            </form>
        </div>
    </div>
<script src="assets/js/admin-settings-ui.js"></script>
<div id="toast-container" class="toast-container"></div>
</body>
</html>

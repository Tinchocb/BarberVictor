<?php
require_once "config/branding.php";
require_once "config/conexion.php";

// SEGURIDAD: No usar ID secuencial. Usar Token.
$token = $_GET['t'] ?? '';
$reserva = null;

if ($token) {
    // Buscamos por token_cancelacion que es único y aleatorio
    $stmt = $conn->prepare("SELECT r.*, b.nombre as barbero FROM reservas r JOIN barberos b ON r.id_barbero = b.id_barbero WHERE r.token_cancelacion = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $reserva = $res->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#050505">
    <title>¡Reserva Exitosa! | <?= BRAND_NAME ?></title>
    <link rel="stylesheet" href="assets/css/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background: var(--bg-deep-black);
        }
        
        .success-container {
            max-width: 500px;
            padding: 50px 40px;
            text-align: center;
            background: var(--glass-effect);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success-icon {
            font-size: 5rem;
            color: #10b981;
            margin-bottom: 25px;
            animation: bounce 0.6s ease;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .success-title {
            color: var(--accent-gold);
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .success-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: 40px;
        }
        
        .reserva-info {
            text-align: left;
            margin: 30px 0;
            padding: 25px;
            background: rgba(197, 160, 89, 0.05);
            border-left: 3px solid var(--accent-gold);
            border-radius: 8px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 12px 0;
            color: var(--text-secondary);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--accent-gold);
        }
        
        .info-value {
            color: var(--text-primary);
        }
        
        .email-note {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 20px 0;
            padding: 15px;
            background: rgba(197, 160, 89, 0.03);
            border-radius: 8px;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 1rem;
            padding: 20px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .btn-volver {
            display: inline-block;
            margin-top: 30px;
            width: 100%;
            text-decoration: none;
        }
        
        @media (max-width: 480px) {
            .success-container {
                padding: 35px 25px;
            }
            .success-icon {
                font-size: 4rem;
            }
            .success-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <?php if ($reserva): ?>
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="success-title">¡Turno Confirmado!</h1>
            <p class="success-subtitle">Tu reserva ha sido procesada exitosamente</p>
            
            <div class="reserva-info">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-user"></i> Cliente:</span>
                    <span class="info-value"><?= htmlspecialchars($reserva['cliente']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-calendar"></i> Fecha:</span>
                    <span class="info-value"><?= date('d/m/Y', strtotime($reserva['fecha_hora'])) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-clock"></i> Hora:</span>
                    <span class="info-value"><?= date('H:i', strtotime($reserva['fecha_hora'])) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-scissors"></i> Profesional:</span>
                    <span class="info-value"><?= htmlspecialchars($reserva['barbero']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-cut"></i> Servicio:</span>
                    <span class="info-value"><?= htmlspecialchars($reserva['servicio']) ?></span>
                </div>
            </div>
            
            <div class="email-note">
                <i class="fas fa-envelope"></i> Comprobante enviado a: <strong><?= htmlspecialchars($reserva['email']) ?></strong>
            </div>
        <?php else: ?>
            <div class="success-icon" style="color: #ef4444;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h1 class="success-title" style="color: #ef4444;">¡Oops!</h1>
            <p class="success-subtitle">No pudimos encontrar tu reserva</p>
            
            <div class="error-message">
                No se encontraron los datos del turno o el enlace es inválido.
            </div>
        <?php endif; ?>

        <a href="index.php" class="btn-gold btn-volver">
            <i class="fas fa-home"></i> VOLVER AL INICIO
        </a>
    </div>
</body>
</html>
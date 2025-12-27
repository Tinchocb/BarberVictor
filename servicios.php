<?php
/**
 * SERVICIOS - PÁGINA PREMIUM
 * ==========================
 * Obtiene servicios de BD de forma segura
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . "/config/branding.php";
require_once __DIR__ . "/config/conexion.php";

$servicios = [];

// Obtener servicios de forma segura
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT id, nombre, precio, descripcion FROM servicios WHERE activo = 1 ORDER BY orden ASC");
        if ($stmt && $stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($row = $resultado->fetch_assoc()) {
                $servicios[] = [
                    'id' => intval($row['id']),
                    'nombre' => htmlspecialchars($row['nombre']),
                    'precio' => floatval($row['precio']),
                    'descripcion' => htmlspecialchars($row['descripcion'] ?? 'Servicio de calidad premium')
                ];
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Error servicios: " . $e->getMessage());
    }
}

// Servicios por defecto si BD está vacía
if (empty($servicios)) {
    $servicios = [
        ['id' => 1, 'nombre' => 'Perfilado de Barba', 'precio' => 15000, 'descripcion' => 'Perfilado profesional de barba con toalla caliente y productos premium'],
        ['id' => 2, 'nombre' => 'Corte Clásico', 'precio' => 12000, 'descripcion' => 'Corte tradicional con técnica clásica, perfecto para looks atemporales'],
        ['id' => 3, 'nombre' => 'Corte Clásico + Barba', 'precio' => 18000, 'descripcion' => 'Corte + perfilado de barba con toalla caliente y hidratación premium'],
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#050505">
    <title>Servicios | <?= BRAND_NAME ?></title>
    <link rel="stylesheet" href="assets/css/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/animations-premium.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
</head>
<body>
    <a href="https://wa.me/<?= CONTACT_WHATSAPP ?>" class="whatsapp-float" target="_blank" title="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <header class="topbar">
        <div class="logo"><i class="fas fa-scissors"></i> <?= BRAND_NAME ?></div>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="servicios.php" class="active">Servicios</a></li>
                <li><a href="reserva.php">Reservar</a></li>
                <li><a href="mis_turnos.php">Mis Turnos</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <main class="container section-padding" style="min-height: calc(100vh - 200px);">
        <div class="text-center fade-in-up">
            <h1 class="text-gold" style="margin-bottom: 20px;">NUESTROS SERVICIOS</h1>
            <p class="text-muted" style="max-width: 600px; margin: 0 auto 80px;">
                Experiencia premium en cada detalle, diseñada para el caballero moderno.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
            <?php foreach ($servicios as $index => $servicio): ?>
            <div class="card-premium fade-in-up" style="display:flex; flex-direction:column; justify-content:space-between;">
                <div>
                    <div class="text-gold" style="font-size: 2.5rem; margin-bottom: 20px;">
                        <i class="fas fa-cut"></i>
                    </div>
                    <h3 style="margin-bottom:10px;"><?= $servicio['nombre'] ?></h3>
                    <p class="text-muted" style="margin-bottom:20px;">
                        <?= $servicio['descripcion'] ?>
                    </p>
                </div>
                <div>
                    <div class="text-gold" style="font-size: 1.5rem; font-weight: 700; margin-bottom: 15px;">
                        $<?= number_format($servicio['precio'], 0, ',', '.') ?>
                    </div>
                    <a href="reserva.php?servicio=<?= urlencode($servicio['nombre']) ?>" class="btn btn-primary btn-full">
                        RESERVAR AHORA
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <footer style="background: var(--bg-panel); border-top: 1px solid var(--border-subtle); padding: 40px 20px; text-align: center; margin-top: auto;">
        <p class="text-muted">&copy; 2025 <?= BRAND_NAME ?>. Todos los derechos reservados.</p>
    </footer>

    <!-- PREMIUM ANIMATION SCRIPTS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="assets/js/animations.js?v=<?= time(); ?>"></script>
</body>
</html>
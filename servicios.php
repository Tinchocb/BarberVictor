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
        ['id' => 1, 'nombre' => 'Corte Clásico', 'precio' => 12000, 'descripcion' => 'Corte tradicional con técnica clásica, perfecto para looks atemporales'],
        ['id' => 2, 'nombre' => 'Corte Clásico + Barba', 'precio' => 18000, 'descripcion' => 'Corte + perfilado de barba con toalla caliente y hidratación premium'],
        ['id' => 3, 'nombre' => 'Perfilado de Barba', 'precio' => 15000, 'descripcion' => 'Perfilado profesional de barba con toalla caliente y productos premium']
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
    <link rel="stylesheet" href="assets/css/animations.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/buttonsPremium.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/stylesServicios.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        /* Servicios - estilo más sutil y consistente */
        .service-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 28px 22px;
            border-radius: 12px;
            min-height: 320px;
            background: linear-gradient(180deg, rgba(20,20,20,0.96), rgba(18,18,18,0.98));
            border: 1px solid rgba(197,160,89,0.06);
            box-shadow: 0 8px 24px rgba(0,0,0,0.45);
            transition: var(--transition-default);
            position: relative;
            overflow: hidden;
            opacity: 1;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
            opacity: 0.9;
            transform-origin: left;
        }

        .service-card:focus-within {
            outline: 2px solid rgba(197,160,89,0.08);
            box-shadow: 0 12px 30px rgba(197,160,89,0.06);
        }

        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 40px rgba(0,0,0,0.55), 0 6px 18px rgba(197,160,89,0.06);
            border-color: rgba(197,160,89,0.12);
        }

        .service-icon {
            font-size: 2.6rem;
            color: var(--accent-gold);
            margin-bottom: 18px;
            transition: transform 0.45s var(--ease-default), filter 0.45s var(--ease-default);
            display: inline-block;
        }

        .service-card:hover .service-icon {
            transform: scale(1.08);
            filter: drop-shadow(0 6px 12px rgba(197,160,89,0.08));
        }

        .service-title {
            color: var(--text-primary);
            margin: 10px 0 12px;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.6px;
        }

        .service-desc {
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 0.98rem;
            flex-grow: 1;
        }

        .service-price {
            font-size: 1.6rem;
            color: var(--accent-gold);
            font-weight: 700;
            margin: 18px 0;
            transition: transform 0.28s var(--ease-default), text-shadow 0.28s var(--ease-default);
            display: inline-block;
        }

        .service-card:hover .service-price {
            transform: translateY(-2px) scale(1.03);
            text-shadow: 0 0 10px rgba(197, 160, 89, 0.25);
        }

        @media (max-width: 1024px) {
            .service-card { padding: 22px 18px; min-height: 300px; }
            .service-icon { font-size: 2.4rem; }
            .service-title { font-size: 1.18rem; }
            .service-price { font-size: 1.4rem; }
        }

        @media (max-width: 600px) {
            .service-card { padding: 18px 14px; min-height: auto; }
            .service-icon { font-size: 2rem; margin-bottom: 12px; }
            .service-title { font-size: 1.05rem; }
        }
    </style>
</head>
<body>
    <a href="https://wa.me/<?= CONTACT_WHATSAPP ?>" class="whatsapp-flotante" target="_blank" title="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <header>
        <div class="logo"><i class="fas fa-scissors"></i> <?= BRAND_NAME ?></div>
        <nav>
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="servicios.php" class="active">Servicios</a></li>
                <li><a href="reserva.php">Reservar</a></li>
                <li><a href="mis_turnos.php">Mis Turnos</a></li>
            </ul>
        </nav>
    </header>

    <main style="min-height: calc(100vh - 200px);">
        <section class="container" style="padding: 100px 20px; text-align: center;">
            <h1 class="text-gold-gradient" data-aos="fade-down" style="font-size: 3.2rem; margin-bottom: 20px; font-weight: 800;">NUESTROS SERVICIOS</h1>
            <p class="text-secondary" data-aos="fade-up" style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 80px; max-width: 600px; margin-left: auto; margin-right: auto;">
                Experiencia premium en cada detalle
            </p>

            <div style="max-width: 1300px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 40px;">
                <?php foreach ($servicios as $index => $servicio): ?>
                <div class="card-glass service-card" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div>
                        <div class="service-icon">
                            <i class="fas fa-cut"></i>
                        </div>
                        <h3 class="service-title"><?= $servicio['nombre'] ?></h3>
                        <p class="service-desc">
                            <?= $servicio['descripcion'] ?>
                        </p>
                    </div>
                    <div>
                        <div class="service-price">
                            $<?= number_format($servicio['precio'], 0, ',', '.') ?>
                        </div>
                        <a href="reserva.php?servicio=<?= urlencode($servicio['nombre']) ?>" class="btn-gold" style="display: block; width: 100%; padding: 14px 20px; border-radius: 8px;">
                            RESERVAR AHORA
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    
    <footer style="background: rgba(0,0,0,0.3); border-top: 1px solid var(--border-primary); padding: 40px 20px; text-align: center; color: var(--text-secondary);">
        <p>&copy; 2025 <?= BRAND_NAME ?>. Todos los derechos reservados.</p>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            if (typeof AOS !== 'undefined') {
                AOS.init({ duration: 1000, once: true, mirror: false });
                // Ensure animations trigger even if elements were added dynamically
                AOS.refreshHard();
            } else {
                // If AOS failed to load, reveal service cards via CSS fallback
                document.querySelectorAll('.service-card').forEach(function(el){
                    el.style.opacity = 1;
                });
            }
        });
    </script>
</body>
</html>
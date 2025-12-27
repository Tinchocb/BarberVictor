<?php
/**
 * MIS TURNOS - PÁGINA PARA CONSULTAR RESERVAS
 * ============================================
 * Sistema de búsqueda y gestión de reservas por DNI/Email
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . "/config/branding.php";
require_once __DIR__ . "/config/conexion.php";

$resultados = [];
$busqueda = trim($_GET['q'] ?? '');

// Búsqueda de reservas
if (!empty($busqueda)) {
    try {
        $stmt = $conn->prepare("
            SELECT id, cliente, email, id_cliente, telefono, fecha_hora, id_barbero, servicio, pago, estado, token_cancelacion 
            FROM reservas 
            WHERE (id_cliente LIKE ? OR email LIKE ?) 
            AND estado IN ('activa', 'completada')
            ORDER BY fecha_hora DESC
            LIMIT 20
        ");
        $query = "%$busqueda%";
        $stmt->bind_param("ss", $query, $query);
        
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($row = $resultado->fetch_assoc()) {
                $resultados[] = $row;
            }
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error búsqueda turnos: " . $e->getMessage());
    }
}

// Obtener barberos para mostrar nombres
$barberos = [];
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT id_barbero, nombre FROM barberos WHERE activo = 1");
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $barberos[$row['id_barbero']] = $row['nombre'];
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Error barberos: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#050505">
    <title>Mis Turnos | <?= BRAND_NAME ?></title>
    <link rel="stylesheet" href="assets/css/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/animations.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/buttonsPremium.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/stylesmisTurnos.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        .search-hero {
            padding: 80px 20px;
            text-align: center;
            background: linear-gradient(135deg, rgba(197, 160, 89, 0.05) 0%, rgba(197, 160, 89, 0.02) 100%);
            border-bottom: 1px solid var(--border-primary);
        }
        
        .hero-title {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--text-primary);
            font-weight: 800;
        }
        
        .hero-desc {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 40px;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .search-input {
            flex: 1;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all var(--duration-normal);
        }
        
        .search-input::placeholder {
            color: var(--text-muted);
        }
        
        .search-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--accent-gold);
            box-shadow: 0 0 15px rgba(197, 160, 89, 0.2);
        }
        
        .search-btn {
            padding: 14px 40px;
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-light));
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all var(--duration-normal);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.95rem;
        }
        
        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(197, 160, 89, 0.4);
        }
        
        .results-container {
            padding: 60px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .results-title {
            font-size: 1.8rem;
            color: var(--text-primary);
            margin-bottom: 30px;
            font-weight: 700;
        }
        
        .turno-card {
            background: var(--glass-effect);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all var(--duration-normal);
        }
        
        .turno-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .turno-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .turno-date {
            font-size: 1.4rem;
            color: var(--accent-gold);
            font-weight: 700;
        }
        
        .turno-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-activa {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid #10b981;
        }
        
        .status-completada {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
            border: 1px solid #3b82f6;
        }
        
        .turno-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            padding: 15px;
            background: rgba(197, 160, 89, 0.05);
            border-left: 3px solid var(--accent-gold);
            border-radius: 6px;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .info-value {
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 500;
        }
        
        .turno-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.85rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all var(--duration-normal);
            font-weight: 600;
        }
        
        .btn-cancelar {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid #ef4444;
        }
        
        .btn-cancelar:hover {
            background: #ef4444;
            color: #fff;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .no-results-icon {
            font-size: 3.5rem;
            color: var(--text-muted);
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.2rem;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .turno-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .turno-info {
                grid-template-columns: 1fr;
            }
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
                <li><a href="servicios.php">Servicios</a></li>
                <li><a href="reserva.php">Reservar</a></li>
                <li><a href="mis_turnos.php" class="active">Mis Turnos</a></li>
            </ul>
        </nav>
    </header>

    <div class="search-hero" data-aos="fade-down">
        <h1 class="hero-title">MIS RESERVAS</h1>
        <p class="hero-desc">Ingresá tu DNI o email para gestionar tus turnos</p>
        <form class="search-form" method="GET" data-aos="fade-up">
            <input type="text" name="q" class="search-input" placeholder="Tu DNI o Email..." value="<?= htmlspecialchars($busqueda) ?>" required>
            <button type="submit" class="search-btn">BUSCAR</button>
        </form>
    </div>

    <div class="results-container">
        <?php if (!empty($busqueda)): ?>
            <?php if (empty($resultados)): ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h2 style="color: var(--text-primary); margin-bottom: 10px;">No se encontraron turnos</h2>
                    <p>No hay reservas activas con los datos ingresados.</p>
                </div>
            <?php else: ?>
                <div class="results-title">
                    <i class="fas fa-calendar-check"></i> <?= count($resultados) ?> Resultado<?= count($resultados) > 1 ? 's' : '' ?>
                </div>
                
                <?php foreach ($resultados as $turno): ?>
                <div class="turno-card" data-aos="fade-up">
                    <div class="turno-header">
                        <div class="turno-date">
                            <i class="fas fa-calendar"></i>
                            <?= date('d/m/Y H:i', strtotime($turno['fecha_hora'])) ?>
                        </div>
                        <span class="turno-status status-<?= $turno['estado'] ?>">
                            <?= ucfirst($turno['estado']) ?>
                        </span>
                    </div>
                    
                    <div class="turno-info">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-user"></i> Cliente</div>
                            <div class="info-value"><?= htmlspecialchars($turno['cliente']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-scissors"></i> Profesional</div>
                            <div class="info-value"><?= htmlspecialchars($barberos[$turno['id_barbero']] ?? 'N/A') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-cut"></i> Servicio</div>
                            <div class="info-value"><?= htmlspecialchars($turno['servicio']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-credit-card"></i> Pago</div>
                            <div class="info-value"><?= htmlspecialchars($turno['pago']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                            <div class="info-value"><?= htmlspecialchars($turno['email']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-phone"></i> Teléfono</div>
                            <div class="info-value"><?= htmlspecialchars($turno['telefono']) ?></div>
                        </div>
                    </div>
                    
                    <?php if ($turno['estado'] === 'activa' && strtotime($turno['fecha_hora']) > time()): ?>
                    <div class="turno-actions">
                        <a href="controllers/cancelar_reserva.php?token=<?= urlencode($turno['token_cancelacion']) ?>" class="btn-sm btn-cancelar" onclick="return confirm('¿Deseas cancelar este turno?')">
                            <i class="fas fa-times"></i> Cancelar Turno
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h2 style="color: var(--text-primary); margin-bottom: 10px;">Búsqueda de Turnos</h2>
                <p>Ingresá tu DNI o email para ver tus reservas</p>
            </div>
        <?php endif; ?>
    </div>

    <footer style="background: rgba(0,0,0,0.3); border-top: 1px solid var(--border-primary); padding: 40px 20px; text-align: center; color: var(--text-secondary); margin-top: 60px;">
        <p>&copy; 2025 <?= BRAND_NAME ?>. Todos los derechos reservados.</p>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });
    </script>
</body>
</html>
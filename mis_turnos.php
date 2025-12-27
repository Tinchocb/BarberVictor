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
                <li><a href="servicios.php">Servicios</a></li>
                <li><a href="reserva.php">Reservar</a></li>
                <li><a href="mis_turnos.php" class="active">Mis Turnos</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <main class="container section-padding" style="min-height: calc(100vh - 200px);">
        <div class="card-premium fade-in-up" style="max-width:800px; margin:0 auto; text-align:center; padding:40px;">
             <h1 class="text-gold" style="margin-bottom:15px;">MIS RESERVAS</h1>
             <p class="text-muted" style="margin-bottom:30px;">Consultá el estado de tus turnos buscando por tu Email o DNI</p>
             
             <form class="search-form" style="display:flex; gap:10px; flex-wrap:wrap;">
                 <input type="text" name="q" class="form-control" placeholder="Ingresá tu Email o DNI" value="<?= htmlspecialchars($busqueda) ?>" style="flex:1; min-width:200px;">
                 <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search" style="margin-right:8px;"></i> BUSCAR
                 </button>
             </form>
        </div>

        <div style="margin-top:50px; max-width:800px; margin-left:auto; margin-right:auto;">
             <?php if (!empty($resultados)): ?>
                 <h3 class="text-muted text-center" style="margin-bottom:30px;">Resultados de Búsqueda</h3>
                 <?php foreach ($resultados as $k => $r): ?>
                     <div class="card-premium fade-in-up" style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; animation-delay: <?= $k * 0.1 ?>s;">
                        <div>
                            <div class="text-gold" style="font-size:1.2rem; font-weight:bold; margin-bottom:5px;">
                                <i class="fas fa-calendar-day"></i> <?= date('d/m/Y', strtotime($r['fecha_hora'])) ?> 
                                <span style="color:#fff; margin-left:10px;"><i class="fas fa-clock"></i> <?= date('H:i', strtotime($r['fecha_hora'])) ?> hs</span>
                            </div>
                            <div class="text-muted" style="margin-bottom:5px;">
                                <i class="fas fa-cut"></i> <?= htmlspecialchars($r['servicio']) ?>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-user-tie"></i> <?= isset($barberos[$r['id_barbero']]) ? htmlspecialchars($barberos[$r['id_barbero']]) : 'Barbero' ?>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <?php 
                                $estadoClass = 'text-muted';
                                if($r['estado'] == 'activa') $estadoClass = 'text-gold';
                                if($r['estado'] == 'completada') $estadoClass = 'text-muted'; 
                            ?>
                            <div class="text-muted" style="font-weight:bold; text-transform:uppercase; border:1px solid currentColor; padding:5px 15px; border-radius:50px; display:inline-block; font-size:0.8rem;">
                                <?= htmlspecialchars($r['estado']) ?>
                            </div>
                            <?php if($r['estado'] == 'activa'): ?>
                                <div style="margin-top:10px;">
                                    <a href="cancelar_turno.php?token=<?= $r['token_cancelacion'] ?>" class="text-muted" style="font-size:0.85rem; text-decoration:underline;">Cancelar Turno</a>
                                </div>
                            <?php endif; ?>
                        </div>
                     </div>
                 <?php endforeach; ?>
             <?php elseif (!empty($busqueda)): ?>
                 <div class="card-premium fade-in-up text-center text-muted">
                    <i class="fas fa-search" style="font-size:3rem; margin-bottom:20px; opacity:0.5;"></i>
                    <p>No se encontraron reservas activas o completadas con ese dato.</p>
                 </div>
             <?php endif; ?>
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
<?php
// Archivo: BarberiaVictorBarberClub/configuracion.php
require_once "config/conexion.php";

// SEGURIDAD: Control de sesión
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['acceso_autorizado']) || $_SESSION['acceso_autorizado'] !== true) {
    header("Location: login.php");
    exit();
}

// Generar CSRF Token si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$msg = "";
$tipo_msg = "";

// --- PROCESAR FORMULARIOS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("❌ Error de seguridad: Token CSRF inválido.");
    }

    try {
        // 1. ACCIONES BARBERO (ELIMINAR / TOGGLE)
        if (isset($_POST['accion_barbero'])) {
            $id = filter_input(INPUT_POST, 'id_barbero', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception("ID de barbero inválido.");

            if ($_POST['accion_barbero'] === 'eliminar') {
                // Verificar si tiene reservas futuras antes de borrar (Constraint check)
                $stmt = $conn->prepare("DELETE FROM barberos WHERE id_barbero = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $msg = "Barbero eliminado correctamente.";
                    $tipo_msg = "success";
                } else {
                    throw new Exception("No se puede eliminar (posiblemente tenga reservas asociadas). Intenta desactivarlo.");
                }
                $stmt->close();
            } elseif ($_POST['accion_barbero'] === 'toggle') {
                $nuevo_estado = filter_input(INPUT_POST, 'nuevo_estado', FILTER_VALIDATE_INT);
                $stmt = $conn->prepare("UPDATE barberos SET activo = ? WHERE id_barbero = ?");
                $stmt->bind_param("ii", $nuevo_estado, $id);
                $stmt->execute();
                $msg = "Estado del barbero actualizado.";
                $tipo_msg = "success";
                $stmt->close();
            }
        }

        // 2. AGREGAR BARBERO
        if (isset($_POST['add_barbero'])) {
            $nombre = trim($_POST['nombre_barbero']);
            $avatar = $_POST['avatar_barbero'];
            
            if (empty($nombre)) throw new Exception("El nombre es obligatorio.");
            
            $stmt = $conn->prepare("INSERT INTO barberos (nombre, activo, avatar) VALUES (?, 1, ?)");
            $stmt->bind_param("ss", $nombre, $avatar);
            $stmt->execute();
            $msg = "Barbero agregado exitosamente.";
            $tipo_msg = "success";
            $stmt->close();
        }

        // 3. AUSENCIAS
        if (isset($_POST['ausencia_barbero'])) {
            $idb = filter_input(INPUT_POST, 'id_barbero_ausencia', FILTER_VALIDATE_INT);
            $ini = $_POST['fecha_inicio'];
            $fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : $ini;
            $mot = trim($_POST['motivo_ausencia']);

            $stmt = $conn->prepare("INSERT INTO ausencias (id_barbero, fecha_inicio, fecha_fin, motivo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $idb, $ini, $fin, $mot);
            $stmt->execute();
            $msg = "Ausencia registrada.";
            $tipo_msg = "success";
            $stmt->close();
        }

        // 4. PRECIOS
        if (isset($_POST['update_precio'])) {
            $id = filter_input(INPUT_POST, 'id_servicio', FILTER_VALIDATE_INT);
            $pr = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);

            if (!$id || !$pr) throw new Exception("Datos de precio inválidos.");

            // Obtener precio anterior para historial
            $stmt = $conn->prepare("SELECT nombre, precio FROM servicios WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $act = $res->fetch_assoc();
            $stmt->close();

            // Actualizar
            $stmt = $conn->prepare("UPDATE servicios SET precio = ? WHERE id = ?");
            $stmt->bind_param("di", $pr, $id);
            $stmt->execute();
            $stmt->close();

            // Historial (Verificamos si existe la tabla primero, aunque idealmente debería existir siempre)
            if ($conn->query("SHOW TABLES LIKE 'historial_precios'")->num_rows > 0) {
                $stmt = $conn->prepare("INSERT INTO historial_precios (servicio, precio_anterior, precio_nuevo) VALUES (?, ?, ?)");
                $stmt->bind_param("sdd", $act['nombre'], $act['precio'], $pr);
                $stmt->execute();
                $stmt->close();
            }
            $msg = "Precio actualizado.";
            $tipo_msg = "success";
        }

        // 5. BLOQUEOS / FERIADOS
        if (isset($_POST['bloquear_dia'])) {
            $f = $_POST['fecha_bloqueo'];
            
            // Use 'bloqueos' table (no motivo field in DB)
            $stmt = $conn->prepare("INSERT INTO bloqueos (fecha) VALUES (?)");
            $stmt->bind_param("s", $f);
            $stmt->execute();
            $msg = "Día bloqueado correctamente.";
            $tipo_msg = "success";
            $stmt->close();
        }

        // 6. ELIMINAR ITEMS VARIOS
        if (isset($_POST['eliminar_item'])) {
            $id = filter_input(INPUT_POST, 'id_eliminar', FILTER_VALIDATE_INT);
            // Whitelist de tablas permitidas para evitar inyección en nombre de tabla
            $tabla = ($_POST['tipo'] === 'bloqueo') ? 'bloqueos' : 'ausencias';
            
            if ($id) {
                $stmt = $conn->prepare("DELETE FROM $tabla WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $msg = "Elemento eliminado.";
                $tipo_msg = "success";
                $stmt->close();
            }
        }

    } catch (Exception $e) {
        $msg = "Error: " . $e->getMessage();
        $tipo_msg = "danger";
        error_log($e->getMessage()); // Log error interno
    }
}

// Consultas para la vista
$barberos = $conn->query("SELECT * FROM barberos");
$servicios = $conn->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY id ASC"); // Solo servicios activos, ordenados por ID
// Use 'bloqueos' table (not bloqueos_dias)
$bloqueos = $conn->query("SELECT * FROM bloqueos WHERE fecha >= CURDATE() ORDER BY fecha ASC");

$ausencias = $conn->query("SELECT a.*, b.nombre FROM ausencias a JOIN barberos b ON a.id_barbero = b.id_barbero WHERE a.fecha_inicio >= CURDATE()");

$historial = ($conn->query("SHOW TABLES LIKE 'historial_precios'")->num_rows > 0) 
    ? $conn->query("SELECT * FROM historial_precios ORDER BY fecha DESC LIMIT 5") 
    : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración | Victor Barber Club</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600&family=Open+Sans:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/animations.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/stylesConfiguracion.css?v=<?= time(); ?>">
</head>
<body>
<div class="topbar">
    <div class="logo">⚙️ CONFIGURACIÓN</div>
    <a href="pedidos.php" class="btn-back">Volver</a>
</div>

<div class="container">
    <?php if($msg): ?>
        <div class="alert <?= $tipo_msg ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="grid-config stagger-children">
        
        <div class="card">
            <h3><i class="fas fa-users"></i> Personal</h3>
            <form method="POST" style="margin-bottom:20px; border-bottom:1px solid #333; padding-bottom:15px;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div style="display:flex; gap:10px;">
                    <input type="text" name="nombre_barbero" placeholder="Nombre" style="flex:2; margin:0;" required>
                    <select name="avatar_barbero" style="flex:1; margin:0;">
                        <option value="💈" selected>💈</option>
                        <option value="😎">😎</option>
                        <option value="🧔">🧔</option>
                        <option value="✂️">✂️</option>
                    </select>
                </div>
                <button type="submit" name="add_barbero" class="btn-action" style="margin-top:10px;">+ Agregar</button>
            </form>
            <div style="max-height:300px; overflow-y:auto;">
                <?php if($barberos): $barberos->data_seek(0); while($b = $barberos->fetch_assoc()): ?>
                    <div class="staff-item">
                        <div>
                            <?= $b['avatar'] ?> <?= htmlspecialchars($b['nombre']) ?> 
                            <small style="color:<?= $b['activo']?'#10b981':'#666' ?>">
                                <?= $b['activo']?'ACTIVO':'INACTIVO' ?>
                            </small>
                        </div>
                        <div style="display:flex;">
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="accion_barbero" value="toggle">
                                <input type="hidden" name="id_barbero" value="<?= $b['id_barbero'] ?>">
                                <input type="hidden" name="nuevo_estado" value="<?= $b['activo']?0:1 ?>">
                                <button class="btn-icon" title="Cambiar Estado"><i class="fas fa-power-off"></i></button>
                            </form>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('¿Estás seguro de borrar este barbero permanentemente?');">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="accion_barbero" value="eliminar">
                                <input type="hidden" name="id_barbero" value="<?= $b['id_barbero'] ?>">
                                <button class="btn-icon btn-del" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; endif; ?>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-calendar-times"></i> Ausencias</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <label>Barbero</label>
                <select name="id_barbero_ausencia">
                    <?php if($barberos): $barberos->data_seek(0); while($b = $barberos->fetch_assoc()): ?>
                        <option value="<?= $b['id_barbero'] ?>"><?= htmlspecialchars($b['nombre']) ?></option>
                    <?php endwhile; endif; ?>
                </select>
                <div style="display:flex; gap:10px;">
                    <div style="flex:1;"><label>Desde</label><input type="date" name="fecha_inicio" required></div>
                    <div style="flex:1;"><label>Hasta (Opcional)</label><input type="date" name="fecha_fin"></div>
                </div>
                <label>Motivo</label><input type="text" name="motivo_ausencia" placeholder="Ej: Médico" required>
                <button type="submit" name="ausencia_barbero" class="btn-action">Guardar Ausencia</button>
            </form>
            <div class="list-container">
                <?php if($ausencias): while($a = $ausencias->fetch_assoc()): ?>
                    <div class="list-item">
                        <div>
                            <strong><?= htmlspecialchars($a['nombre']) ?></strong>: <?= htmlspecialchars($a['motivo']) ?>
                            <br><small><?= date('d/m', strtotime($a['fecha_inicio'])) ?> - <?= $a['fecha_fin'] ? date('d/m', strtotime($a['fecha_fin'])) : '' ?></small>
                        </div>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="tipo" value="ausencia">
                            <input type="hidden" name="id_eliminar" value="<?= $a['id'] ?>">
                            <button name="eliminar_item" class="btn-icon btn-del">&times;</button>
                        </form>
                    </div>
                <?php endwhile; endif; ?>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-tags"></i> Precios</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <label>Servicio</label>
                <select name="id_servicio">
                    <?php if($servicios): $servicios->data_seek(0); while($s = $servicios->fetch_assoc()): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?> ($<?= $s['precio'] ?>)</option>
                    <?php endwhile; endif; ?>
                </select>
                <label>Nuevo Precio</label><input type="number" step="0.01" name="precio" placeholder="$" required>
                <button type="submit" name="update_precio" class="btn-action">Actualizar</button>
            </form>
            <div class="list-container">
                <label style="margin-top:10px;">HISTORIAL CAMBIOS</label>
                <?php if($historial): foreach($historial as $h): ?>
                    <div class="list-item">
                        <span><?= htmlspecialchars($h['servicio']) ?></span>
                        <span>
                            <s style="color:#666">$<?= $h['precio_anterior'] ?></s> 
                            <b style="color:#10b981">$<?= $h['precio_nuevo'] ?></b>
                        </span>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-store-slash"></i> Feriados (Cierre)</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <label>Fecha</label><input type="date" name="fecha_bloqueo" required>
                <button type="submit" name="bloquear_dia" class="btn-action" style="background:#444; color:#fff;">Bloquear Día</button>
            </form>
            <div class="list-container">
                <?php if($bloqueos && $bloqueos->num_rows > 0): while($b = $bloqueos->fetch_assoc()): ?>
                    <div class="list-item">
                        <div>
                            <?= date('d/m/Y', strtotime($b['fecha'])) ?>
                        </div>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="tipo" value="bloqueo">
                            <input type="hidden" name="id_eliminar" value="<?= $b['id'] ?>">
                            <button name="eliminar_item" class="btn-icon btn-del">&times;</button>
                        </form>
                    </div>
                <?php endwhile; else: echo "<small>No hay feriados registrados.</small>"; endif; ?>
            </div>
        </div>

    </div>
</div>
</body>
</html>
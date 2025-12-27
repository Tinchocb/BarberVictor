<?php
// BarberiaVictorBarberClub/reserva.php

// 1. CONFIGURACIÓN INICIAL
// Desactivamos mostrar errores en pantalla para que no rompan el HTML
// Los errores graves se capturan en try-catch
ini_set('display_errors', 0);
error_reporting(E_ALL);

$barberos = [];
$precios_db = [];
$error_sistema = false;
$mensaje_error = "";

// 2. INTENTO DE CONEXIÓN
// Ruta absoluta segura al archivo de configuración
$conexion_path = __DIR__ . "/config/conexion.php";

if (file_exists($conexion_path)) {
    require_once $conexion_path;

    // Verificamos si la conexión ($conn) llegó viva desde conexion.php
    if (!isset($conn) || $conn === null) {
        $error_sistema = true;
        $mensaje_error = "No hay conexión con la base de datos.";
    } else {
        // 3. CONSULTAS A LA BASE DE DATOS
        try {
            // Obtener Barberos
            $sql_barberos = "SELECT id_barbero, nombre, avatar FROM barberos WHERE activo = 1";
            $res = $conn->query($sql_barberos);
            if ($res) {
                while($row = $res->fetch_assoc()) { $barberos[] = $row; }
            }

            // Obtener Servicios
            $sql_servicios = "SELECT nombre, precio FROM servicios WHERE activo = 1";
            $res_p = $conn->query($sql_servicios);
            if ($res_p) {
                while($row = $res_p->fetch_assoc()) { 
                    $precios_db[$row['nombre']] = $row['precio']; 
                }
            }
        } catch (Exception $e) {
            error_log("Error SQL en reserva.php: " . $e->getMessage());
            // No seteamos error_sistema true aquí para intentar mostrar la página 
            // aunque sea parcialmente, pero si querés podés hacerlo.
        }
    }
} else {
    $error_sistema = true;
    $mensaje_error = "Falta archivo de configuración.";
}

// Cargar branding para funciones utilitarias (CSRF token, constantes)
require_once __DIR__ . '/config/branding.php';

// Fallback: Si no hay precios, ponemos unos por defecto para que JS no falle
if(empty($precios_db)) { 
    $precios_db = ['Corte Clásico' => 10000, 'Corte Clásico + Barba' => 15000, 'Perfilado de Barba' => 15000]; 
}

// --- COMIENZO DEL HTML ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Turno | Victor Barber Club</title>
    <link rel="stylesheet" href="assets/css/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/animations.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/stylesReserva.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/buttonsPremium.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/buttonsReserva.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/resumenReserva.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/alertasReserva.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    
    <style>
        /* ESTILOS WIZARD ESPECÍFICOS (Requeridos para funcionamiento interno) */
        .date-picker-container { display: flex; align-items: center; justify-content: center; margin-bottom: 20px; gap: 10px; }
        .nav-arrow { background: transparent; border: 1px solid rgba(197,160,89,0.16); color: var(--dorado); width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.18s ease, background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease; box-shadow: 0 8px 20px rgba(0,0,0,0.55); }
        .nav-arrow:hover { background: var(--dorado); color: #000; transform: translateY(-3px); box-shadow: 0 18px 40px rgba(0,0,0,0.6); }
        .date-scroll-wrapper { display: flex; overflow-x: auto; gap: 10px; padding: 10px 5px; scroll-behavior: smooth; width: 100%; scrollbar-width: none; }
        .date-scroll-wrapper::-webkit-scrollbar { display: none; }
        .date-card { min-width: 85px; background: #222; border: 1px solid #444; border-radius: 8px; padding: 15px 5px; text-align: center; cursor: pointer; transition: var(--transition-default); flex-shrink: 0; }
        .date-card.selected { background: #C5A059; border-color: #C5A059; color: #000; }
        .date-card.selected * { color: #000; }
        .date-day-name { display: block; font-size: 0.8rem; color: #888; text-transform: uppercase; margin-bottom: 5px; }
        .date-number { display: block; font-size: 1.5rem; font-weight: bold; color: #fff; }
        .grilla-horarios { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; margin-top: 20px; }
        .btn-horario { background: #222; border: 1px solid #444; color: #fff; padding: 10px; border-radius: 6px; text-align: center; cursor: pointer; font-weight: bold; transition: var(--transition-default); }
        .btn-horario:hover { border-color: #C5A059; color: #C5A059; }
        .btn-horario.seleccionado { background: #C5A059; color: #000; border-color: #C5A059; box-shadow: 0 0 10px rgba(197, 160, 89, 0.4); }
        .btn-horario.ocupado { background: #111; color: #444; border-color: #222; text-decoration: line-through; pointer-events: none; cursor: not-allowed; }
        .resumen-card { background: #1a1a1a; border: 1px solid #C5A059; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .resumen-header { background: #C5A059; color: #000; padding: 10px; font-weight: bold; text-align: center; font-family: 'Oswald', sans-serif; letter-spacing: 1px; border-radius: 6px 6px 0 0; margin: -20px -20px 20px -20px; }
        .resumen-group { margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .resumen-label { color: #C5A059; font-size: 0.8rem; font-weight: bold; display: block; margin-bottom: 5px; }
        .resumen-value { color: #fff; font-size: 1.1rem; }
        .resumen-sub { color: #888; font-size: 0.9rem; margin-top: 2px; display: block; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .botones-pasos { display: flex; gap: 10px; margin-top: 20px; }
        .btn-gold { background: #C5A059; color: #000; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; flex: 1; transition: var(--transition-default); }
        .btn-gold:hover { transform: translateY(-2px); }
        .btn-outline { background: transparent; border: 1px solid #666; color: #ccc; padding: 12px; border-radius: 6px; cursor: pointer; transition: var(--transition-default); }
        .btn-outline:hover { border-color: #C5A059; color: #C5A059; }
        .grid-opciones { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 15px; }
        .card-option { cursor: pointer; position: relative; }
        .card-option input { position: relative; opacity: 0; }
        .card-content { background: #222; border: 1px solid #333; border-radius: 8px; padding: 15px; text-align: center; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; transition: var(--transition-default); }
        .card-option input:checked + .card-content { border-color: #C5A059; background: rgba(197, 160, 89, 0.1); }
        .card-title { font-weight: bold; margin-top: 5px; display: block; color: #fff; }
        .card-price { color: #C5A059; font-weight: bold; font-size: 0.9rem; margin-top: 5px; display: block; }
        .card-icon { font-size: 1.5rem; margin-bottom: 5px; }
        .paso { display: none; }
        .paso.active { display: block; }
        
        /* Contenedor de Error Visible */
        .error-alert { 
            background: rgba(183, 28, 28, 0.2); 
            border: 1px solid #b71c1c; 
            color: #ffcccc; 
            padding: 20px; 
            border-radius: 8px; 
            text-align: center; 
            margin: 40px auto;
            max-width: 600px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">VICTOR BARBER CLUB</div>
        <nav>
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="servicios.php">Servicios</a></li>
                <li><a href="reserva.php" class="active">Reservar</a></li>
                <li><a href="mis_turnos.php">Mis Turnos</a></li>
            </ul>
        </nav>
    </header>

    <main style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
        <h1 style="text-align: center; margin-bottom: 40px; font-family:'Oswald',sans-serif; animation: bounceIn 0.8s cubic-bezier(0.2, 0.9, 0.3, 1); letter-spacing: 2px; color: #C5A059; text-shadow: 0 2px 10px rgba(197,160,89,0.2);">AGENDÁ TU TURNO</h1>
      
        <?php if ($error_sistema): ?>
            <div class="error-alert">
                <h3>⚠️ Servicio no disponible</h3>
                <p><?= htmlspecialchars($mensaje_error) ?></p>
                <p style="margin-top:10px; font-size:0.9rem; color:#aaa;">Verificá la conexión a la base de datos en config/conexion.php</p>
                <a href="https://wa.me/5492964611775" class="btn-gold" style="display:inline-block; margin-top:15px; text-decoration:none;">Reservar por WhatsApp</a>
            </div>
        <?php else: ?>

            <div class="progress-indicator">
                <div class="progress-line"></div>
                <div class="progress-step active" id="ind-1"><div class="progress-circle">1</div><small>Barbero</small></div>
                <div class="progress-step" id="ind-2"><div class="progress-circle">2</div><small>Servicio</small></div>
                <div class="progress-step" id="ind-3"><div class="progress-circle">3</div><small>Datos</small></div>
                <div class="progress-step" id="ind-4"><div class="progress-circle">4</div><small>Fin</small></div>
            </div>

            <form action="controllers/guardar_reserva.php" method="POST" id="formReserva">
                <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF(); ?>">
                
                <div class="paso paso-1 active">
                    <h3 style="color: #C5A059; text-align:center; margin-bottom:20px;">Seleccioná tu Barbero</h3>
                    <div class="grid-opciones">
                        <?php if (!empty($barberos)): ?>
                            <?php foreach($barberos as $b): ?>
                            <label class="card-option">
                                <input type="radio" name="id_barbero" value="<?= htmlspecialchars($b['id_barbero']) ?>" required>
                                <div class="card-content">
                                    <div class="card-icon"><?= !empty($b['avatar']) ? $b['avatar'] : '💈' ?></div>
                                    <span class="card-title"><?= htmlspecialchars($b['nombre']) ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="grid-column: 1/-1; text-align: center; padding: 30px; border: 1px dashed #444; border-radius: 8px;">
                                <p style="color:#aaa;">No se encontraron barberos activos.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="botones-pasos"><button type="button" class="btn-gold" style="width:100%" onclick="cambiarPaso(2)">Siguiente</button></div>
                </div>

                <div class="paso paso-2">
                    <h3 style="color: #C5A059; text-align:center; margin-bottom:20px;">Elegí el Servicio</h3>
                    <div class="grid-opciones">
                        <?php foreach($precios_db as $nombre => $precio): ?>
                        <label class="card-option">
                            <div style="margin-top:10px">
                            <input type="radio" name="servicio" value="<?= htmlspecialchars($nombre) ?>" required>
                            <div class="card-content">
                                <div class="card-icon">💈</div>
                                <span class="card-title"><?= htmlspecialchars($nombre) ?></span>
                                <span class="card-price">$<?= number_format($precio, 0, ',', '.') ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <h3 style="margin-top:40px; color: #ccc; text-align: center;">📅 Elegí el Día</h3>
                    <div class="date-picker-container">
                        <button type="button" class="nav-arrow" onclick="moverScroll(-1)"><i class="fas fa-chevron-left"></i></button>
                        <div class="date-scroll-wrapper" id="date-scroll"></div>
                        <button type="button" class="nav-arrow" onclick="moverScroll(1)"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    
                    <input type="hidden" name="fecha" id="fecha_seleccionada" required>
                    <input type="hidden" name="hora" id="hora-seleccionada" required>
                    
                    <h3 id="titulo-horarios" style="display:none; color: #ccc; text-align: center; margin-top:20px;">⏰ Elegí la Hora</h3>
                    <div class="grilla-horarios" id="horarios-container"></div>
                    
                    <div class="botones-pasos">
                        <button type="button" class="btn-outline" onclick="cambiarPaso(1)">Atrás</button>
                        <button type="button" class="btn-gold" onclick="cambiarPaso(3)">Siguiente</button>
                    </div>
                </div>
            
                <div class="paso paso-3">
                    <h3 style="color: #C5A059; text-align:center; margin-bottom:20px;">Tus Datos</h3>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <input type="text" name="cliente" id="cliente" placeholder="Nombre Completo" required class="input-premium" style="padding:12px; background:#222; border:1px solid #444; color:#fff; border-radius:6px;">
                        <input type="email" name="email" id="email" placeholder="Email" required class="input-premium" style="padding:12px; background:#222; border:1px solid #444; color:#fff; border-radius:6px;">
                        <input type="number" name="id_cliente" id="dni" placeholder="DNI (solo números)" required class="input-premium" style="padding:12px; background:#222; border:1px solid #444; color:#fff; border-radius:6px;">
                        <input type="tel" name="telefono" id="telefono" placeholder="WhatsApp / Teléfono" required class="input-premium" style="padding:12px; background:#222; border:1px solid #444; color:#fff; border-radius:6px;">
                    </div>
                    
                    <label style="margin-top:20px; display:block; color:#C5A059; margin-bottom:10px; font-weight:bold;">Método de Pago:</label>
                    <div class="grid-opciones" style="grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));">
                        <label class="card-option">
                            <input type="radio" name="pago" value="Efectivo" checked>
                            <div class="card-content">
                                <span class="pay-emoji">💵</span>
                                <span class="pay-label">Efectivo</span>
                            </div>
                        </label>
                        <label class="card-option">
                            <input type="radio" name="pago" value="Transferencia">
                            <div class="card-content">
                                <span class="pay-emoji">📱</span>
                                <span class="pay-label">Transferencia</span>
                            </div>
                        </label>
                        <label class="card-option">
                            <input type="radio" name="pago" value="Local">
                            <div class="card-content">
                                <span class="pay-emoji">💳</span>
                                <span class="pay-label">Pago en Local</span>
                            </div>
                        </label>
                    </div>
                    
                    <div class="botones-pasos">
                        <button type="button" class="btn-outline" onclick="cambiarPaso(2)">Atrás</button>
                        <button type="button" class="btn-gold" onclick="mostrarResumen()">Ver Resumen</button>
                    </div>
                </div>

                <div class="paso paso-4">
                    <h3 style="color: #C5A059; text-align:center; margin-bottom:20px;">Confirmar Datos</h3>
                    
                    <div class="resumen-card">
                        <div class="resumen-header">RESUMEN DEL TURNO</div>
                        <div class="resumen-body">
                            <div class="resumen-group">
                                <span class="resumen-label">SERVICIO</span>
                                <div class="resumen-value" id="res-servicio">-</div>
                                <div class="resumen-sub">Con <span id="res-barbero" style="color:#fff;">-</span></div>
                            </div>
                            <div class="resumen-group">
                                <span class="resumen-label">FECHA Y HORA</span>
                                <div class="resumen-value"><span id="res-fecha">-</span> a las <span id="res-hora" style="color:#C5A059; font-weight:bold;">-</span></div>
                            </div>
                            <div class="resumen-group">
                                <span class="resumen-label">CLIENTE</span>
                                <div class="resumen-value" id="res-cliente" style="margin-bottom:5px;">-</div>
                                <div class="info-grid">
                                    <div><span class="resumen-sub">DNI:</span> <span id="res-dni" style="color:#fff;">-</span></div>
                                    <div><span class="resumen-sub">Tel:</span> <span id="res-telefono" style="color:#fff;">-</span></div>
                                </div>
                            </div>
                            <div class="resumen-group" style="border:none; padding-bottom:0;">
                                <span class="resumen-label">PAGO</span>
                                <div class="resumen-value" id="res-pago" style="color:#C5A059; font-weight:bold;">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="botones-pasos">
                        <button type="button" class="btn-outline" onclick="cambiarPaso(3)">Editar</button>
                        <button type="submit" class="btn-gold" onclick="return validarEnvio()">CONFIRMAR RESERVA</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        generarFechas();
        
        const params = new URLSearchParams(window.location.search);
        const barberoID = params.get('barbero');
        if (barberoID) {
            const radio = document.querySelector(`input[name="id_barbero"][value="${barberoID}"]`);
            if (radio) {
                radio.checked = true;
                setTimeout(() => cambiarPaso(2), 300);
            }
        }

        document.querySelectorAll('input[name="id_barbero"]').forEach(r => {
            r.addEventListener('click', () => {
                setTimeout(() => cambiarPaso(2), 250);
                const f = document.getElementById('fecha_seleccionada').value;
                if(f) cargarHorarios(f);
            });
        });
    });

    function moverScroll(d) { document.getElementById('date-scroll').scrollLeft += (d * 200); }

    function generarFechas() {
        const c = document.getElementById('date-scroll');
        const dias = ['DOM', 'LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB'];
        const hoy = new Date();
        for (let i = 0; i < 21; i++) {
            let f = new Date(); 
            f.setDate(hoy.getDate() + i);
            if (f.getDay() === 0 || f.getDay() === 1) continue; 
            
            const val = f.toISOString().split('T')[0];
            const div = document.createElement('div');
            div.className = 'date-card';
            div.innerHTML = `<span class="date-day-name">${dias[f.getDay()]}</span><span class="date-number">${f.getDate()}</span>`;
            div.onclick = () => {
                document.querySelectorAll('.date-card').forEach(x => x.classList.remove('selected'));
                div.classList.add('selected');
                document.getElementById('fecha_seleccionada').value = val;
                document.getElementById('titulo-horarios').style.display = 'block';
                cargarHorarios(val);
            };
            c.appendChild(div);
        }
    }
    
    function cargarHorarios(fecha) {
        const barbero = document.querySelector('input[name="id_barbero"]:checked');
        const cont = document.getElementById('horarios-container');
        if (!barbero) return;
        
        cont.innerHTML = '<p style="text-align:center; color:#C5A059; grid-column:1/-1;">⏳ Cargando horarios...</p>';
        
        fetch(`controllers/verificar_disponibilidad.php?fecha=${fecha}&id_barbero=${barbero.value}`)
            .then(r => r.json())
            .then(data => {
                cont.innerHTML = '';
                if (data.bloqueado) { 
                    cont.innerHTML = `<div style="grid-column:1/-1; color:#ff6b6b; text-align:center; padding:10px; border:1px solid #ff6b6b; border-radius:6px;">🚫 ${data.mensaje}</div>`; 
                    return; 
                }
                
                const horas = ["10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00"];
                
                horas.forEach(h => {
                    const btn = document.createElement('div');
                    btn.className = 'btn-horario';
                    btn.textContent = h;
                    if (data.ocupados && data.ocupados.includes(h)) { 
                        btn.classList.add('ocupado'); 
                    } else {
                        btn.onclick = () => {
                            document.querySelectorAll('.btn-horario').forEach(b => b.classList.remove('seleccionado'));
                            btn.classList.add('seleccionado');
                            document.getElementById('hora-seleccionada').value = h;
                        };
                    }
                    cont.appendChild(btn);
                });
            })
            .catch(err => {
                console.error(err);
                cont.innerHTML = '<p style="text-align:center; color:red; grid-column:1/-1;">No se pudieron cargar los horarios.</p>';
            });
    }

    function cambiarPaso(n) {
        if (n === 2 && !document.querySelector('input[name="id_barbero"]:checked')) { alert("Seleccioná un barbero."); return; }
        if (n === 3) {
            if (!document.querySelector('input[name="servicio"]:checked')) { alert("Seleccioná un servicio."); return; }
            if (!document.getElementById('fecha_seleccionada').value) { alert("Seleccioná una fecha."); return; }
            if (!document.getElementById('hora-seleccionada').value) { alert("Seleccioná una hora."); return; }
        }
        document.querySelectorAll('.paso').forEach(p => p.classList.remove('active'));
        document.querySelector('.paso-' + n).classList.add('active');
        document.querySelectorAll('.progress-step').forEach((s, i) => s.classList.toggle('active', i < n));
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    function mostrarResumen() {
        const nom = document.getElementById('cliente').value;
        const mail = document.getElementById('email').value;
        const dni_val = document.getElementById('dni').value;
        const tel = document.getElementById('telefono').value;

        if(!nom || !mail || !dni_val || !tel) { alert("Completá todos tus datos personales."); return; }
        
        const barberoEl = document.querySelector('input[name="id_barbero"]:checked');
        const servicioEl = document.querySelector('input[name="servicio"]:checked');
        
        document.getElementById('res-barbero').innerText = barberoEl.parentNode.querySelector('.card-title').innerText;
        document.getElementById('res-servicio').innerText = servicioEl.parentNode.querySelector('.card-title').innerText;
        document.getElementById('res-fecha').innerText = document.getElementById('fecha_seleccionada').value;
        document.getElementById('res-hora').innerText = document.getElementById('hora-seleccionada').value;
        document.getElementById('res-pago').innerText = document.querySelector('input[name="pago"]:checked').value;
        
        document.getElementById('res-cliente').innerText = nom;
        document.getElementById('res-dni').innerText = dni_val;
        document.getElementById('res-telefono').innerText = tel;
        
        cambiarPaso(4);
    }

    function validarEnvio() {
        if (!document.getElementById('hora-seleccionada').value) { alert("Falta seleccionar hora."); return false; }
        return true;
    }
    </script>
</body>
</html>
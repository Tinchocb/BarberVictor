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
    <link rel="stylesheet" href="assets/css/animations-premium.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
</head>
<body>
    <header class="topbar">
        <div class="logo"><i class="fas fa-cut"></i> VICTOR BARBER CLUB</div>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="servicios.php">Servicios</a></li>
                <li><a href="reserva.php" class="active">Reservar</a></li>
                <li><a href="mis_turnos.php">Mis Turnos</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <main class="container section-padding">
        <div class="text-center fade-in-up" onclick="void(0)">
            <h1 class="text-gold" style="margin-bottom:30px;">AGENDÁ TU TURNO</h1>
        </div>
      
        <?php if ($error_sistema): ?>
            <div class="card-premium" style="max-width:600px; margin:0 auto; text-align:center; border-color:var(--accent-error);">
                <i class="fas fa-exclamation-triangle" style="font-size:3rem; color:var(--accent-error); margin-bottom:20px;"></i>
                <h3>Servicio no disponible</h3>
                <p><?= htmlspecialchars($mensaje_error) ?></p>
                <a href="https://wa.me/5492964611775" class="btn btn-primary" style="margin-top:20px;">Reservar por WhatsApp</a>
            </div>
        <?php else: ?>

            <div class="progress-indicator">
                <div class="progress-line"></div>
                <div class="progress-step active" id="ind-1"><div class="progress-circle">1</div><small>Barbero</small></div>
                <div class="progress-step" id="ind-2"><div class="progress-circle">2</div><small>Servicio</small></div>
                <div class="progress-step" id="ind-3"><div class="progress-circle">3</div><small>Datos</small></div>
                <div class="progress-step" id="ind-4"><div class="progress-circle">4</div><small>Fin</small></div>
            </div>

            <form action="controllers/guardar_reserva.php" method="POST" id="formReserva" style="max-width:800px; margin:0 auto;">
                <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF(); ?>">
                
                <!-- PASO 1: BARBERO -->
                <div class="paso paso-1 active">
                    <h3 class="text-center text-gold">Seleccioná tu Barbero</h3>
                    <div class="grid-opciones">
                        <?php if (!empty($barberos)): ?>
                            <?php foreach($barberos as $b): ?>
                            <label class="card-option">
                                <input type="radio" name="id_barbero" value="<?= htmlspecialchars($b['id_barbero']) ?>" required>
                                <div class="card-content">
                                    <div class="card-icon"><?= !empty($b['avatar']) ? $b['avatar'] : '<i class="fas fa-user-tie"></i>' ?></div>
                                    <span class="card-title"><?= htmlspecialchars($b['nombre']) ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="card-premium text-center">No se encontraron barberos activos.</div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-primary btn-full" onclick="cambiarPaso(2)">Siguiente <i class="fas fa-arrow-right" style="margin-left:10px;"></i></button>
                </div>

                <!-- PASO 2: SERVICIO Y FECHA -->
                <div class="paso paso-2">
                    <h3 class="text-center text-gold">Elegí el Servicio</h3>
                    <div class="grid-opciones">
                        <?php foreach($precios_db as $nombre => $precio): ?>
                        <label class="card-option">
                            <input type="radio" name="servicio" value="<?= htmlspecialchars($nombre) ?>" required>
                            <div class="card-content">
                                <div class="card-icon"><i class="fas fa-cut"></i></div>
                                <span class="card-title"><?= htmlspecialchars($nombre) ?></span>
                                <span class="card-price">$<?= number_format($precio, 0, ',', '.') ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <h3 class="text-center text-muted" style="margin-top:40px;">📅 Elegí el Día</h3>
                    <div class="date-picker-container">
                        <button type="button" class="nav-arrow" onclick="moverScroll(-1)"><i class="fas fa-chevron-left"></i></button>
                        <div class="date-scroll-wrapper" id="date-scroll"></div>
                        <button type="button" class="nav-arrow" onclick="moverScroll(1)"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    
                    <input type="hidden" name="fecha" id="fecha_seleccionada" required>
                    <input type="hidden" name="hora" id="hora-seleccionada" required>
                    
                    <h3 id="titulo-horarios" class="text-center text-muted hidden" style="margin-top:20px;">⏰ Elegí la Hora</h3>
                    <div class="grilla-horarios" id="horarios-container"></div>
                    
                    <div style="display:flex; gap:10px; margin-top:30px;">
                        <button type="button" class="btn btn-outline" style="flex:1;" onclick="cambiarPaso(1)">Atrás</button>
                        <button type="button" class="btn btn-primary" style="flex:1;" onclick="cambiarPaso(3)">Siguiente</button>
                    </div>
                </div>
            
                <!-- PASO 3: DATOS -->
                <div class="paso paso-3">
                    <h3 class="text-center text-gold">Tus Datos</h3>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div class="form-group"><input type="text" name="cliente" id="cliente" placeholder="Nombre Completo" required class="form-control"></div>
                        <div class="form-group"><input type="email" name="email" id="email" placeholder="Email" required class="form-control"></div>
                        <div class="form-group"><input type="number" name="id_cliente" id="dni" placeholder="DNI (solo números)" required class="form-control"></div>
                        <div class="form-group"><input type="tel" name="telefono" id="telefono" placeholder="WhatsApp / Teléfono" required class="form-control"></div>
                    </div>
                    
                    <label class="form-label text-gold" style="margin-top:20px;">Método de Pago:</label>
                    <div class="grid-opciones" style="grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));">
                        <label class="card-option">
                            <input type="radio" name="pago" value="Efectivo" checked>
                            <div class="card-content">
                                <span style="font-size:1.5rem;">💵</span>
                                <span class="card-title">Efectivo</span>
                            </div>
                        </label>
                        <label class="card-option">
                            <input type="radio" name="pago" value="Transferencia">
                            <div class="card-content">
                                <span style="font-size:1.5rem;">📱</span>
                                <span class="card-title">Transferencia</span>
                            </div>
                        </label>
                        <label class="card-option">
                            <input type="radio" name="pago" value="Débito/Crédito">
                            <div class="card-content">
                                <span style="font-size:1.5rem;">💳</span>
                                <span class="card-title">Tarjeta</span>
                            </div>
                        </label>
                    </div>
                    
                    <div style="display:flex; gap:10px; margin-top:30px;">
                        <button type="button" class="btn btn-outline" style="flex:1;" onclick="cambiarPaso(2)">Atrás</button>
                        <button type="button" class="btn btn-primary" style="flex:1;" onclick="mostrarResumen()">Confirmar</button>
                    </div>
                </div>

                <!-- PASO 4: CONFIRMACIÓN -->
                <div class="paso paso-4">
                    <div class="resumen-card fade-in-up">
                        <div class="resumen-header">RESUMEN DEL TURNO</div>
                        <div class="info-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                            <div><span class="text-gold">Barbero:</span> <div id="res-barbero" class="text-white"></div></div>
                            <div><span class="text-gold">Servicio:</span> <div id="res-servicio" class="text-white"></div></div>
                            <div><span class="text-gold">Fecha:</span> <div id="res-fecha" class="text-white"></div></div>
                            <div><span class="text-gold">Hora:</span> <div id="res-hora" class="text-white"></div></div>
                            <div style="grid-column:1/-1;"><span class="text-gold">Pago:</span> <div id="res-pago" class="text-white"></div></div>
                        </div>
                        <div style="margin-top:20px; padding-top:10px; border-top:1px solid #333;">
                            <div><span class="text-gold">Cliente:</span> <div id="res-cliente" class="text-white"></div></div>
                            <div><span class="text-gold">DNI:</span> <div id="res-dni" class="text-white"></div></div>
                        </div>
                    </div>
                    
                    <div style="display:flex; gap:10px; margin-top:30px;">
                        <button type="button" class="btn btn-outline" style="flex:1;" onclick="cambiarPaso(3)">Corregir</button>
                        <button type="submit" class="btn btn-primary" style="flex:1;" onclick="return validarEnvio()">Confirmar Reserva</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <!-- SCRIPTS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="assets/js/animations.js?v=<?= time(); ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        generarFechas();
        setupBarberoEvent();
        
        // Handle Service Pre-selection
        const params = new URLSearchParams(window.location.search);
        const servicioParam = params.get('servicio');
        if (servicioParam) {
            const decodedService = decodeURIComponent(servicioParam).replace(/\+/g, ' '); 
            const radio = document.querySelector(`input[name="servicio"][value="${decodedService}"]`);
            if (radio) radio.checked = true;
        }
    });

    function setupBarberoEvent() {
        // Auto-advance if barbero pre-selected via URL
        const params = new URLSearchParams(window.location.search);
        const barberoID = params.get('barbero');
        if (barberoID) {
            const radio = document.querySelector(`input[name="id_barbero"][value="${barberoID}"]`);
            if (radio) { radio.checked = true; setTimeout(() => cambiarPaso(2), 500); }
        }
        
        // Auto-advance on barbero click
        document.querySelectorAll('input[name="id_barbero"]').forEach(r => {
            r.addEventListener('change', () => {
                setTimeout(() => cambiarPaso(2), 300);
                const f = document.getElementById('fecha_seleccionada').value;
                if(f) cargarHorarios(f);
            });
        });
    }

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
            div.innerHTML = `<small class="date-day-name">${dias[f.getDay()]}</small><div class="date-number">${f.getDate()}</div>`;
            div.onclick = () => {
                document.querySelectorAll('.date-card').forEach(x => x.classList.remove('selected'));
                div.classList.add('selected');
                document.getElementById('fecha_seleccionada').value = val;
                document.getElementById('titulo-horarios').classList.remove('hidden');
                cargarHorarios(val);
            };
            c.appendChild(div);
        }
    }
    
    function cargarHorarios(fecha) {
        const barbero = document.querySelector('input[name="id_barbero"]:checked');
        const cont = document.getElementById('horarios-container');
        if (!barbero) return;
        
        cont.innerHTML = '<p class="text-gold" style="grid-column:1/-1; text-align:center;">⏳ Cargando horarios...</p>';
        
        fetch(`controllers/verificar_disponibilidad.php?fecha=${fecha}&id_barbero=${barbero.value}`)
            .then(r => r.json())
            .then(data => {
                cont.innerHTML = '';
                if (data.bloqueado) { 
                    cont.innerHTML = `<div style="grid-column:1/-1; color:var(--accent-error); text-align:center; padding:10px; border:1px solid var(--accent-error); border-radius:6px;">🚫 ${data.mensaje}</div>`; 
                    return; 
                }
                
                const horas = ["10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00"];
                // Animate entry
                gsap.fromTo(cont, { opacity: 0 }, { opacity: 1, duration: 0.5 });

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
                cont.innerHTML = '<p style="text-align:center; color:red; grid-column:1/-1;">Error al cargar horarios</p>';
            });
    }

    let currentStep = 1;
    function cambiarPaso(n) {
        // Validaciones
        if (n === 2 && !document.querySelector('input[name="id_barbero"]:checked')) { alert("Seleccioná un barbero."); return; }
        if (n === 3) {
            if (!document.querySelector('input[name="servicio"]:checked')) { alert("Seleccioná un servicio."); return; }
            if (!document.getElementById('fecha_seleccionada').value) { alert("Seleccioná una fecha."); return; }
            if (!document.getElementById('hora-seleccionada').value) { alert("Seleccioná una hora."); return; }
        }

        const currentEl = document.querySelector('.paso.active');
        const nextEl = document.querySelector('.paso-' + n);
        
        // GSAP Transition
        gsap.to(currentEl, {
            opacity: 0, x: -20, duration: 0.3, onComplete: () => {
                currentEl.classList.remove('active');
                nextEl.classList.add('active');
                gsap.fromTo(nextEl, { opacity: 0, x: 20 }, { opacity: 1, x: 0, duration: 0.3 });
                
                document.querySelectorAll('.progress-step').forEach((s, i) => s.classList.toggle('active', (i + 1) <= n));
                window.scrollTo({ top: 0, behavior: 'smooth' });
                currentStep = n;
            }
        });
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
        document.getElementById('res-pago').innerText = document.querySelector('input[name="pago"]:checked').parentNode.querySelector('.card-title').innerText;
        
        document.getElementById('res-cliente').innerText = nom;
        document.getElementById('res-dni').innerText = dni_val;
        
        cambiarPaso(4);
    }

    function validarEnvio() {
        if (!document.getElementById('hora-seleccionada').value) { alert("Falta seleccionar hora."); return false; }
        return true;
    }
    </script>
</body>
</html>
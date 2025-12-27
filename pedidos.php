<?php
// Archivo: BarberiaVictorBarberClub/pedidos.php
session_start();
require_once "config/conexion.php"; 

if (!isset($_SESSION['acceso_autorizado']) || $_SESSION['acceso_autorizado'] !== true) { 
    header("Location: login.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Victor Barber Club</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/animations.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/animations-premium.css?v=<?= time(); ?>">

</head>
<body>

<div class="topbar">
    <div class="logo">VICTOR BARBER CLUB</div>
    <div class="nav-actions">
        <a href="admin_settings.php" class="btn-nav"><i class="fa   s fa-cog"></i> Config</a>
        <a href="controllers/logout.php" class="btn-nav" style="border-color:#dc3545; color:#dc3545;"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</div>

<div class="container animate-entry">
    
    <div class="toolbar">
        <div class="date-group">
            <span class="date-label">Desde</span>
            <input type="date" id="filtro-desde">
        </div>
        <div class="date-group">
            <span class="date-label">Hasta</span>
            <input type="date" id="filtro-hasta">
        </div>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="input-busqueda" class="search-input" placeholder="Buscar por cliente, barbero o DNI..." onkeyup="aplicarFiltrosLocales()">
        </div>

        <button class="btn-refresh" onclick="cargarDatos()">
            <i class="fas fa-sync-alt"></i> ACTUALIZAR
        </button>
    </div>

    <div class="hero-next next-turn-card">
        <div class="hero-info" id="proximo-turno-info">
            <h4><i class="fas fa-clock"></i> Próximo Turno</h4>
            <div style="color:#666;">Cargando...</div>
        </div>
        <div class="hero-action" id="proximo-turno-action">
            </div>
    </div>

    <div class="kpi-strip">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="kpi-content">
                <div class="kpi-label">Total Turnos</div>
                <div class="kpi-val" id="kpi-total">0</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="kpi-content">
                <div class="kpi-label">Pendientes</div>
                <div class="kpi-val" id="kpi-pendientes" style="color:var(--info)">0</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-user-slash"></i></div>
            <div class="kpi-content">
                <div class="kpi-label">Ausentes</div>
                <div class="kpi-val" id="kpi-ausentes" style="color:var(--danger)">0</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="kpi-content">
                <div class="kpi-label">Ganancia Estimada</div>
                <div class="kpi-val" id="kpi-ingresos" style="color:var(--gold)">$0</div>
            </div>
        </div>
    </div>

    <div class="chart-box">
        <canvas id="grafico-ingresos"></canvas>
    </div>

    <h3 style="color:var(--text); font-family:'Oswald',sans-serif; margin-bottom:15px; font-weight:400; border-bottom:1px solid #333; padding-bottom:10px;">
        <i class="fas fa-list-ul" style="color:var(--gold); margin-right:10px;"></i> LISTADO DE TURNOS
    </h3>
    
    <div class="status-tabs">
        <button class="tab-btn active" onclick="setTab('all', this)">Todos</button>
        <button class="tab-btn" onclick="setTab('activa', this)">Pendientes</button>
        <button class="tab-btn" onclick="setTab('completada', this)">Completados</button>
        <button class="tab-btn" onclick="setTab('ausente', this)">Ausentes</button>
        <button class="tab-btn" onclick="setTab('cancelada', this)">Cancelados</button>
    </div>

    <div class="table-wrapper">
        <div class="table-header">
            <div>FECHA</div> <div>HORA</div> <div>CLIENTE</div> <div>DNI</div> <div>TELÉFONO</div> <div>BARBERO</div> <div>SERVICIO</div> <div>ESTADO</div> <div style="text-align:center;">ACCIONES</div>
        </div>
        <div id="tabla-turnos">
            <div style="padding:40px; text-align:center; color:#666;">Cargando datos...</div>
        </div>
        <div id="pagination-controls" class="pagination"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function escapeHtml(text) { return text ? text.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;") : ''; }

let datosGlobales = [];
let filtroEstadoActual = 'all';
let grafico = null;

document.addEventListener('DOMContentLoaded', () => {
    // FECHAS POR DEFECTO: HOY -> +7 DÍAS
    const hoy = new Date();
    const hasta = new Date();
    hasta.setDate(hoy.getDate() + 7);
    
    document.getElementById('filtro-desde').value = hoy.toISOString().split('T')[0];
    document.getElementById('filtro-hasta').value = hasta.toISOString().split('T')[0];
    
    cargarDatos();
});

function cargarDatos() {
    const fd = new FormData();
    fd.append('desde', document.getElementById('filtro-desde').value);
    fd.append('hasta', document.getElementById('filtro-hasta').value);

    fetch('controllers/ajax_dashboard.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.error) { console.error(data.error); return; }

        document.getElementById('kpi-total').innerText = data.kpi.total_turnos;
        document.getElementById('kpi-pendientes').innerText = data.kpi.pendientes;
        document.getElementById('kpi-ausentes').innerText = data.kpi.ausentes;
        document.getElementById('kpi-ingresos').innerText = '$' + new Intl.NumberFormat('es-AR').format(data.kpi.ingresos);

        // Render Próximo Turno
        const p = data.proximo;
        if(p) {
            document.getElementById('proximo-turno-info').innerHTML = `
                <h4><i class="fas fa-clock"></i> Próximo Turno (${p.fecha})</h4>
                <div>
                    <span class="hero-time">${p.hora}</span> 
                    <span class="hero-client">${escapeHtml(p.cliente)}</span>
                </div>
                <div class="hero-details">${escapeHtml(p.servicio)} con ${escapeHtml(p.barbero)}</div>
            `;
            const btnWsp = p.telefono ? `<a href="https://wa.me/${p.telefono}" target="_blank" class="btn-wsp"><i class="fab fa-whatsapp"></i> Contactar</a>` : '';
            document.getElementById('proximo-turno-action').innerHTML = btnWsp;
        } else {
            document.getElementById('proximo-turno-info').innerHTML = `<div style="color:#666; font-size:1.2rem;">Sin clientes en espera</div>`;
            document.getElementById('proximo-turno-action').innerHTML = '';
        }

        actualizarGrafico(data.grafico);
        datosGlobales = data.tabla;
        aplicarFiltrosLocales();
    })
    .catch(e => console.error(e));
}

function aplicarFiltrosLocales() {
    const txt = document.getElementById('input-busqueda').value.toLowerCase();
    
    const filtrados = datosGlobales.filter(t => {
        const okEstado = (filtroEstadoActual === 'all') || (t.estado === filtroEstadoActual);
        // Buscamos por Cliente, Barbero O DNI
        const okTxt = t.cliente.toLowerCase().includes(txt) || 
                      t.barbero.toLowerCase().includes(txt) || 
                      (t.dni && t.dni.includes(txt));
        return okEstado && okTxt;
    });
    renderizarTabla(filtrados);
}

function renderizarTabla(datos) {
    const cont = document.getElementById('tabla-turnos');
    if (datos.length === 0) {
        cont.innerHTML = `<div style="padding:40px; text-align:center; color:#555;">No se encontraron resultados.</div>`;
        return;
    }
    cont.innerHTML = datos.map(t => `
        <div class="table-row">
            <div style="color:#aaa;">${t.fecha.slice(0,5)}</div>
            <div style="color:#fff; font-weight:bold;">${t.hora}</div>
            <div style="color:var(--gold); text-transform:capitalize;">${escapeHtml(t.cliente)}</div>
            <div style="font-size:0.9rem;">${escapeHtml(t.dni)}</div>
            <div>${t.telefono ? `<a href="https://wa.me/${t.telefono}" target="_blank" class="c-wsp"><i class="fab fa-whatsapp"></i> ${t.telefono}</a>` : '--'}</div>
            <div>${escapeHtml(t.barbero)}</div>
            <div style="font-size:0.85rem; color:#ccc;">${escapeHtml(t.servicio)}</div>
            <div><span class="badge bg-${t.estado.toLowerCase()}">${t.estado}</span></div>
            <div style="text-align:center;">
                ${t.estado === 'activa' ? `
                    <button class="btn-icon c-green" onclick="cambiarEstado(${t.id}, 'completada')" title="Completar"><i class="fas fa-check"></i></button>
                    <button class="btn-icon c-red" onclick="cambiarEstado(${t.id}, 'ausente')" title="Ausente"><i class="fas fa-times"></i></button>
                    <button class="btn-icon c-gray" onclick="cambiarEstado(${t.id}, 'cancelada')" title="Cancelar"><i class="fas fa-trash"></i></button>
                ` : '<i class="fas fa-lock" style="color:#333; font-size:0.8rem;"></i>'}
            </div>
        </div>
    `).join('');
}

function setTab(estado, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filtroEstadoActual = estado;
    aplicarFiltrosLocales();
}

function actualizarGrafico(datos) {
    const ctx = document.getElementById('grafico-ingresos').getContext('2d');
    if (grafico) grafico.destroy();
    grafico = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: datos.labels,
            datasets: [{ label: 'Ingresos', data: datos.ingresos, backgroundColor: '#C5A059', borderRadius: 4, barThickness: 30 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: '#222' }, ticks: { color: '#666' } }, x: { grid: { display: false }, ticks: { color: '#666' } } }
        }
    });
}

function cambiarEstado(id, nestado) {
    if (!confirm(`¿Confirmar estado ${nestado.toUpperCase()}?`)) return;
    const fd = new FormData(); fd.append('id', id); fd.append('estado', nestado);
    fetch('controllers/update_estado.php', { method: 'POST', body: fd }).then(r=>r.json()).then(d=>{ if(d.success) cargarDatos(); });
}
</script>

<!-- PREMIUM ANIMATION SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="assets/js/gsap-init.js?v=<?= time(); ?>"></script>
<script src="assets/js/micro-interactions.js?v=<?= time(); ?>"></script>
<script>
    // Animate KPI counters on load
    document.addEventListener('DOMContentLoaded', () => {
        // Will be called after cargarDatos() fetches values
        const originalCargarDatos = cargarDatos;
        window.cargarDatos = function() {
            const result = originalCargarDatos.apply(this, arguments);
            // Animate KPIs after data loads
            setTimeout(() => {
                if (typeof animateCounter === 'function') {
                    const kpiTotal = document.getElementById('kpi-total');
                    const kpiPendientes = document.getElementById('kpi-pendientes');
                    const kpiAusentes = document.getElementById('kpi-ausentes');
                    const kpiIngresos = document.getElementById('kpi-ingresos');
                    
                    if (kpiTotal) animateCounter(kpiTotal, parseInt(kpiTotal.textContent) || 0, 1);
                    if (kpiPendientes) animateCounter(kpiPendientes, parseInt(kpiPendientes.textContent) || 0, 1);
                    if (kpiAusentes) animateCounter(kpiAusentes, parseInt(kpiAusentes.textContent) || 0, 1);
                }
            }, 500);
            return result;
        };
    });
</script>
<div id="toast-container" class="toast-container"></div>
<script src="assets/js/pedidos-ui.js"></script>
</body>
</html>
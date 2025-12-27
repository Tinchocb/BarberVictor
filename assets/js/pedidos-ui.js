// pedidos-ui.js - UI enhancements for dashboard

// Toast helper
function showToast(message, type = 'success') {
    let toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    // force reflow for transition
    void toast.offsetWidth;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Simple pagination (assumes datosGlobales array)
function renderPaginatedTable(data, page = 1, perPage = 20) {
    const start = (page - 1) * perPage;
    const end = start + perPage;
    const slice = data.slice(start, end);
    renderizarTabla(slice);
    // pagination controls (basic)
    const container = document.getElementById('pagination-controls');
    if (!container) return;
    const totalPages = Math.ceil(data.length / perPage);
    let html = '';
    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="btn-page ${i === page ? 'active' : ''}" onclick="renderPaginatedTable(${JSON.stringify(data)}, ${i})">${i}</button>`;
    }
    container.innerHTML = html;
}

// Hook into cargarDatos to use pagination
function cargarDatosConPaginacion() {
    const fd = new FormData();
    fd.append('desde', document.getElementById('filtro-desde').value);
    fd.append('hasta', document.getElementById('filtro-hasta').value);
    fetch('controllers/ajax_dashboard.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.error) { console.error(data.error); return; }
            // update KPIs etc.
            document.getElementById('kpi-total').innerText = data.kpi.total_turnos;
            document.getElementById('kpi-pendientes').innerText = data.kpi.pendientes;
            document.getElementById('kpi-ausentes').innerText = data.kpi.ausentes;
            document.getElementById('kpi-ingresos').innerText = '$' + new Intl.NumberFormat('es-AR').format(data.kpi.ingresos);
            // render with pagination
            datosGlobales = data.tabla;
            renderPaginatedTable(datosGlobales);
            // show toast
            showToast('Datos actualizados', 'success');
        })
        .catch(e => console.error(e));
}

// Replace original cargarDatos call on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    // set default dates (already done in original script, keep)
    const hoy = new Date();
    const hasta = new Date();
    hasta.setDate(hoy.getDate() + 7);
    document.getElementById('filtro-desde').value = hoy.toISOString().split('T')[0];
    document.getElementById('filtro-hasta').value = hasta.toISOString().split('T')[0];
    cargarDatosConPaginacion();
});

// Expose for manual refresh button
function cargarDatos() { cargarDatosConPaginacion(); }

// admin-settings-ui.js - UI enhancements for admin settings page

// Copy email to clipboard
function copyEmail() {
    const emailInput = document.getElementById('email-display');
    if (!emailInput) return;
    const email = emailInput.textContent.trim();
    navigator.clipboard.writeText(email).then(() => {
        showToast('Email copiado al portapapeles', 'success');
    }).catch(() => {
        showToast('Error al copiar el email', 'error');
    });
}

// Password strength meter
function updateStrengthMeter() {
    const pwd = document.getElementById('new-password');
    const meter = document.getElementById('strength-meter');
    if (!pwd || !meter) return;
    const val = pwd.value;
    let strength = 0;
    if (val.length >= 8) strength += 1;
    if (/[A-Z]/.test(val)) strength += 1;
    if (/[a-z]/.test(val)) strength += 1;
    if (/[0-9]/.test(val)) strength += 1;
    if (/[^A-Za-z0-9]/.test(val)) strength += 1;
    const percent = (strength / 5) * 100;
    meter.querySelector('.fill').style.width = percent + '%';
}

// Simple toast (reuse same function from pedidos-ui.js if already loaded)
function showToast(message, type = 'success') {
    let toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    void toast.offsetWidth;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Attach events after DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const copyBtn = document.getElementById('copy-email-btn');
    if (copyBtn) copyBtn.addEventListener('click', copyEmail);
    const pwdInput = document.getElementById('new-password');
    if (pwdInput) pwdInput.addEventListener('input', updateStrengthMeter);
});

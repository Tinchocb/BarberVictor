<?php
/**
 * CONFIGURACIÓN GLOBAL DE MARCA
 * ==============================
 * Archivo centralizado para todas las variables de branding.
 * Permite cambiar la marca global para cualquier cliente.
 */

// ========== IDENTIDAD DE MARCA ==========
define('BRAND_NAME', 'THE BARBER SHOP');
define('BRAND_TAGLINE', 'Estilo, Clase y Tradición');
define('BRAND_DESCRIPTION', 'Cuidamos tu imagen con las mejores técnicas clásicas y modernas.');

// ========== CONTACTO PREDETERMINADO ==========
define('CONTACT_PHONE', '+54 9 296 461-1775');
define('CONTACT_EMAIL', 'info@thebarbershop.com');
define('CONTACT_WHATSAPP', '5492964611775');
define('CONTACT_ADDRESS', 'Tu dirección aquí');

// ========== DATOS DE NEGOCIO ==========
define('BUSINESS_HOURS', '09:00 - 19:00');
define('TIMEZONE', 'America/Argentina/Buenos_Aires');

// ========== PÁGINA ADMIN ==========
define('ADMIN_DEFAULT_EMAIL', 'admin@thebarbershop.com');

// ========== CONFIGURACIÓN DE SESIÓN SEGURA ==========
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'use_only_cookies' => true,
        'use_strict_mode' => true,
        'cookie_httponly' => true,
        'cookie_secure' => false, // Cambiar a true en HTTPS
        'cookie_samesite' => 'Lax'
    ]);
}

// ========== FUNCIONES AUXILIARES ==========

/**
 * Genera un token CSRF único
 */
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica si un token CSRF es válido
 */
function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Establece headers de seguridad
 */
function establecerHeadersSeguridad() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' unpkg.com cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' fonts.googleapis.com cdnjs.cloudflare.com; img-src \'self\' data:; font-src \'self\' fonts.gstatic.com');
}

// Establecer headers de seguridad automáticamente
establecerHeadersSeguridad();

// ========== VARIABLES DE EXPERIENCIA DE USUARIO ==========
define('ITEMS_PER_PAGE', 20);
define('MAX_FILE_UPLOAD_MB', 5);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
?>

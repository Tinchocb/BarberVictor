# 🏆 THE BARBER SHOP - Plantilla Premium White-Label

**Versión 2.0 | Refactorizada Completamente**

Una solución barbería profesional, segura y elegante con estética Dark & Gold, perfect para ser personalizada como plantilla blanca para cualquier cliente.

---

## ✨ Características Principales

### 🔒 Seguridad Enterprise-Grade
- **Prepared Statements** en todas las consultas SQL (prevención de SQL Injection)
- **Tokens CSRF** en formularios críticos
- **Session Management** seguro con opciones HTTPOnly y SameSite
- **Headers de Seguridad** automáticos (X-Content-Type-Options, CSP, etc.)
- **Validación robusta** de entrada en backend
- **Password Hashing** con `password_hash()`
- **Logging de errores** sin exponer detalles técnicos

### 🎨 Diseño Premium
- **Paleta Dark & Gold** unificada en variables CSS
- **Glassmorphism Effect** en tarjetas y formularios
- **Animaciones AOS** (Animate On Scroll) fluidas
- **Responsive completo** (mobile-first, tablet, desktop)
- **Micro-interacciones** con shine effects, transiciones suaves
- **Scroll personalizado** con gradiente dorado

### 🏗️ Arquitectura Limpia
- **Configuración centralizada** en `config/branding.php`
- **CSS unificado** en `assets/css/global.css` (sin dispersión)
- **Funciones reutilizables** para CSRF, seguridad, etc.
- **Separación de lógica** (controllers, views, config)

### ⚡ Funcionalidades
- Sistema de reservas multi-paso (wizard)
- Panel de administración para staff
- Gestión de servicios
- Cancelación de reservas
- Recordatorios por email (PHPMailer)
- Búsqueda de turnos

---

## 🚀 Instalación y Configuración

### 1️⃣ Requisitos
- PHP 7.4+
- MySQL 5.7+
- Servidor Apache con mod_rewrite
- Extensiones: mysqli, openssl, fileinfo

### 2️⃣ Base de Datos
Ejecutar el script SQL:
```bash
mysql -u root -p < database/bd_barberia.sql
```

### 3️⃣ Configuración de Marca (IMPORTANTE)

Editar `config/branding.php`:

```php
// Identidad
define('BRAND_NAME', 'TU BARBERIA AQUÍ');
define('BRAND_TAGLINE', 'Tu lema aquí');
define('BRAND_DESCRIPTION', 'Descripción breve');

// Contacto
define('CONTACT_PHONE', '+54 9 TU_NUMERO');
define('CONTACT_EMAIL', 'info@tudominio.com');
define('CONTACT_WHATSAPP', 'TU_NUMERO_WHATSAPP');
define('CONTACT_ADDRESS', 'Tu dirección aquí');
```

### 4️⃣ Email (PHPMailer)

Configurar SMTP en `controllers/guardar_reserva.php`:

```php
$mail->Host = 'smtp.gmail.com'; // Tu proveedor SMTP
$mail->Username = 'tu-email@gmail.com';
$mail->Password = 'tu-clave-app'; // Usar contraseña de aplicación en Gmail
```

O usar variables de entorno:
```php
$mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
```

### 5️⃣ Permisos y Carpetas

```bash
# Crear carpeta de logs
mkdir -p logs
chmod 777 logs

# Asegurar permisos
chmod 644 config/*.php
chmod 755 config
```

---

## 📁 Estructura de Archivos

```
BarberiaPRO/
├── config/
│   ├── branding.php          # 🔑 CONFIGURACIÓN CENTRALIZADA (editar aquí)
│   └── conexion.php          # Conexión segura a BD
├── controllers/
│   ├── guardar_reserva.php   # Guardar reserva con CSRF
│   ├── cancelar_reserva.php  # Cancelar reserva
│   └── ...
├── assets/
│   ├── css/
│   │   └── global.css        # 🎨 Estilos unificados (Dark & Gold)
│   ├── img/
│   └── fonts/
├── includes/
│   └── PHPMailer/           # Librería email
├── database/
│   └── bd_barberia.sql      # Script BD
├── logs/                     # Logs de errores
├── index.html               # Portada (Hero premium)
├── login.php                # Staff login (Glasmorphism)
├── reserva.php              # Wizard multi-paso
├── servicios.php            # Listado de servicios
├── mis_turnos.php           # Búsqueda de turnos
├── pedidos.php              # Dashboard staff
└── README.md                # Este archivo
```

---

## 🎯 Páginas y Funcionalidades

### 🏠 **index.html** - Portada Premium
- Hero animado con partículas doradas
- Sección de características con Glassmorphism
- Botón WhatsApp flotante
- Footer completo con redes sociales

### 📅 **reserva.php** - Sistema de Reservas
- Wizard en 4 pasos:
  1. Seleccionar profesional
  2. Elegir servicio y horario
  3. Ingresar datos personales
  4. Confirmar y enviar
- Validación robusta en cada paso
- Protección CSRF
- Date picker dinámico
- Resumen antes de confirmar

### 💇 **servicios.php** - Catálogo
- Listado de servicios con precio
- Tarjetas con Glassmorphism
- Enlace directo a reserva
- Consultas seguras a BD

### 🔐 **login.php** - Acceso Staff
- Autenticación segura
- Glasmorphism premium
- CSRF protection
- Session regeneration

### 📊 **pedidos.php** - Dashboard
- Resumen de reservas del día
- KPIs (ingresos, turnos, etc.)
- Tabla de clientes
- Búsqueda y filtros

### 🔍 **mis_turnos.php** - Búsqueda de Turnos
- Búsqueda por DNI o email
- Ver detalles de reserva
- Opción de cancelación

---

## 🔐 Características de Seguridad

### SQL Injection Prevention
✅ **Prepared Statements** en todas las consultas:
```php
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
```

### CSRF Protection
✅ **Tokens únicos por sesión**:
```php
$csrf_token = generarTokenCSRF();
// En formulario:
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
```

### Session Security
✅ **Opciones seguras**:
```php
session_start([
    'use_only_cookies' => true,
    'cookie_httponly' => true,
    'cookie_secure' => false, // true con HTTPS
    'cookie_samesite' => 'Lax'
]);
```

### Password Security
✅ **Hashing con argon2i**:
```php
password_hash($password, PASSWORD_ARGON2I);
```

### XSS Prevention
✅ **Escapado de output**:
```php
<?= htmlspecialchars($variable) ?>
```

---

## 🎨 Paleta de Colores

Variables CSS centralizadas en `assets/css/global.css`:

```css
:root {
    --accent-gold: #C5A059;          /* Oro principal */
    --accent-gold-light: #F4E285;    /* Oro claro */
    --accent-gold-dark: #8E7036;     /* Oro oscuro */
    --bg-deep-black: #050505;        /* Fondo principal */
    --bg-darker: #0a0a0a;            /* Fondo más oscuro */
    --bg-card: #111111;              /* Tarjetas */
    --text-primary: #ffffff;         /* Texto principal */
    --text-secondary: #e0e0e0;       /* Texto secundario */
    --text-tertiary: #aaaaaa;        /* Texto terciario */
    --glass-effect: rgba(255, 255, 255, 0.03);
    --glass-border: rgba(197, 160, 89, 0.2);
}
```

### Ejemplos de Uso:
```html
<!-- Texto dorado animado -->
<h1 class="text-gold-gradient">TÍTULO IMPORTANTE</h1>

<!-- Tarjeta con glassmorphism -->
<div class="card-glass">Contenido aquí</div>

<!-- Botón premium -->
<button class="btn-gold">ACCIÓN</button>
```

---

## 📱 Responsividad

**Mobile-first approach** con breakpoints:
- **480px**: Smartphones pequeños
- **768px**: Tablets
- **1200px**: Escritorio
- **1400px+**: Pantallas grandes

Todos los componentes se adaptan automáticamente sin scroll horizontal.

---

## 🎬 Animaciones Premium

### AOS (Animate On Scroll)
```html
<div data-aos="fade-up" data-aos-duration="1000" data-aos-delay="100">
    Contenido
</div>
```

### Efecto Shine en Botones
```css
.btn-gold::before {
    content: '';
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shine 0.6s;
}
```

### Partículas Flotantes
Animación automática en hero section con pequeños elementos dorados flotando.

---

## 🔧 Personalización para Clientes

### 1. Cambiar Color de Marca
Editar `assets/css/global.css`:
```css
--accent-gold: #TU_COLOR_AQUI;
```

### 2. Cambiar Logo y Nombre
Editar `config/branding.php`:
```php
define('BRAND_NAME', 'NOMBRE_DEL_CLIENTE');
```

### 3. Agregar Logo
Reemplazar logo en `assets/img/logo.png` y actualizar header:
```html
<img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
```

### 4. Personalizar Servicios
Agregar en BD tabla `servicios`:
```sql
INSERT INTO servicios (nombre, precio, descripcion, activo) VALUES
('Servicio Personalizado', 25000, 'Descripción', 1);
```

### 5. Cambiar WhatsApp
Editar en `config/branding.php`:
```php
define('CONTACT_WHATSAPP', 'NUEVO_NUMERO');
```

---

## 📧 Configurar Emails

### Con Gmail
1. Activar "Contraseñas de aplicación" en Google Account
2. Configurar en `controllers/guardar_reserva.php`:
```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'tu-email@gmail.com';
$mail->Password = 'tu-clave-app-16-caracteres';
$mail->Port = 465;
```

### Con Otro Proveedor SMTP
Cambiar valores de host, puerto y credenciales.

---

## 🐛 Solución de Problemas

### Error: "No se puede conectar a BD"
- Verificar credenciales en `config/conexion.php`
- Asegurar que MySQL está corriendo
- Revisar usuario y contraseña

### Error: "Email no se envía"
- Verificar credenciales SMTP
- Activar "contraseñas de aplicación" en Gmail
- Revisar logs en carpeta `logs/`

### Error: "CSRF token inválido"
- Asegurar que sesión está activa
- Verificar que `session_start()` se ejecuta antes

### Página en blanco
- Revisar `logs/errors.log` para detalles
- Activar `display_errors` en development (NO en producción)

---

## 🚀 Deployment

### En Hosting
1. Subir archivos vía FTP
2. Crear BD y ejecutar script SQL
3. Editar `config/branding.php` con datos del cliente
4. Configurar email (SMTP)
5. Establecer permisos: `chmod 644` en PHP, `chmod 755` en directorios
6. Cambiar `cookie_secure` a `true` en HTTPS

### Variables de Entorno
Para mayor seguridad, usar `.env`:
```
DB_HOST=localhost
DB_USER=barberia
DB_PASS=password_segura
SMTP_HOST=smtp.gmail.com
SMTP_USER=email@gmail.com
SMTP_PASS=clave-app
```

---

## 📊 Base de Datos - Tablas Necesarias

```sql
CREATE TABLE usuarios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  nombre VARCHAR(100),
  rol VARCHAR(50),
  activo INT DEFAULT 1
);

CREATE TABLE barberos (
  id_barbero INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100),
  activo INT DEFAULT 1
);

CREATE TABLE servicios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100),
  precio DECIMAL(10,2),
  descripcion TEXT,
  orden INT,
  activo INT DEFAULT 1
);

CREATE TABLE reservas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  fecha_hora DATETIME,
  cliente VARCHAR(100),
  id_barbero INT,
  servicio VARCHAR(100),
  pago VARCHAR(50),
  id_cliente VARCHAR(15),
  telefono VARCHAR(15),
  email VARCHAR(255),
  token_cancelacion VARCHAR(255),
  token_resena VARCHAR(255),
  estado VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 📞 Soporte y Mantenimiento

### Actualizar
- Revisar cambios en `CHANGELOG.md`
- Backup de BD antes de actualizar
- Testear en ambiente de prueba primero

### Monitoreo
- Revisar `logs/errors.log` regularmente
- Hacer backup semanal de BD
- Revisar alertas de seguridad

---

## 📜 Licencia

Plantilla Premium White-Label para clientes. Todos los derechos reservados.

---

## 🎉 ¡Listo para Usar!

Tu barbería está completamente configurada con:
✅ Seguridad Enterprise  
✅ Diseño Premium Dark & Gold  
✅ Sistema de Reservas Completo  
✅ Responsivo en Todos los Dispositivos  
✅ Listo para Personalización  

**Contáctanos para más features o personalizaciones.**


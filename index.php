<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#000000">
    <meta name="description" content="Barbería de lujo - Experiencia premium en cada corte">
    <title>THE BARBER SHOP | Experiencia Premium</title>

    <!-- Google Fonts Premium -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@300;400;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="assets/css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/animations-premium.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


</head>

<body>
    <!-- PRELOADER ULTRA PREMIUM -->
    <div id="preloader">
        <div class="preloader-content">
            <div class="preloader-logo">THE BARBER</div>
            <div class="preloader-spinner"></div>
            <div class="preloader-line"></div>
        </div>
    </div>

    <!-- WHATSAPP FLOTANTE -->
    <a href="https://wa.me/5492964121212" class="whatsapp-float" target="_blank" rel="noopener noreferrer"
        title="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- HEADER ULTRA PREMIUM -->
    <header id="header">
        <div class="logo">
            <i class="fas fa-cut"></i>
            THE BARBER SHOP
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Inicio</a></li>
                <li><a href="servicios.php">Servicios</a></li>
                <li><a href="reserva.php">Reservar</a></li>
                <li><a href="mis_turnos.php">Mis Turnos</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- HERO SECTION MINIMALIST -->
        <section class="hero-wrapper parallax-container">
            <div class="hero-bg parallax-bg" data-parallax-speed="0.5"></div>
            <div class="hero-overlay"></div>

            <div class="hero-content parallax-content">
                <div class="hero-badge" data-reveal="fade-down">Experiencia Premium</div>
                <h1 class="hero-title hero-text-reveal" data-reveal="fade-up" data-reveal-delay="0.2">
                    ESTILO<br>CLASE<br>TRADICIÓN
                </h1>
                <p class="hero-subtitle" data-reveal="fade-up" data-reveal-delay="0.4">
                    "Donde la artesanía se encuentra con la excelencia"
                </p>
                <div class="hero-cta" data-reveal="fade-up" data-reveal-delay="0.6">
                    <a href="reserva.php" class="btn btn-primary magnetic-button">Reservar Turno</a>
                    <a href="servicios.php" class="btn btn-outline">Ver Servicios</a>
                </div>
            </div>
        </section>

        <!-- FEATURES SECTION ULTRA PREMIUM -->
        <section class="features-section section-padding">
            <div class="container">
                <div class="section-header text-center fade-in-up">
                    <span class="text-gold" style="letter-spacing:2px; font-size:0.9rem;">Nuestra Experiencia</span>
                    <h2 class="section-title">Excellence in Every Detail</h2>
                    <div style="width:50px; height:2px; background:var(--gold-primary); margin:20px auto;"></div>
                </div>

                <div class="features-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:30px; margin-top:50px;">
                    <div class="card-premium fade-in-up">
                        <div class="feature-number text-gold" style="font-size:3rem; opacity:0.3; font-weight:700;">01</div>
                        <i class="fas fa-cut text-gold" style="font-size:2rem; margin:15px 0;"></i>
                        <h3>Maestros Artesanos</h3>
                        <p class="text-muted">Nuestro equipo de barberos profesionales combina técnicas tradicionales con las últimas tendencias.</p>
                    </div>

                    <div class="card-premium fade-in-up">
                        <div class="feature-number text-gold" style="font-size:3rem; opacity:0.3; font-weight:700;">02</div>
                        <i class="fas fa-glass-whiskey text-gold" style="font-size:2rem; margin:15px 0;"></i>
                        <h3>Ambiente Exclusivo</h3>
                        <p class="text-muted">Disfruta de un espacio diseñado para tu comodidad, con música seleccionada y bebidas premium.</p>
                    </div>

                    <div class="card-premium fade-in-up">
                        <div class="feature-number text-gold" style="font-size:3rem; opacity:0.3; font-weight:700;">03</div>
                        <i class="fas fa-clock text-gold" style="font-size:2rem; margin:15px 0;"></i>
                        <h3>Reservas Digitales</h3>
                        <p class="text-muted">Sistema de reservas completamente digital. Reserva tu turno en segundos, sin esperas.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER PREMIUM -->
    <footer style="background:var(--bg-panel); padding:60px 0; margin-top:auto; border-top:1px solid rgba(255,255,255,0.05);">
        <div class="container" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:40px;">
            <div class="footer-col">
                <h3 class="text-gold">THE BARBER SHOP</h3>
                <p class="text-muted">Elevando el estándar de la barbería masculina con técnica, estilo y dedicación artesanal.</p>
            </div>
            <div class="footer-col">
                <h3>CONTACTO</h3>
                <p class="text-muted"><i class="fas fa-map-marker-alt text-gold"></i> Av. Principal 1234</p>
                <p class="text-muted"><i class="fab fa-whatsapp text-gold"></i> +54 9 2964 123456</p>
                <p class="text-muted"><i class="fas fa-envelope text-gold"></i> info@thebarbershop.com</p>
            </div>
            <div class="footer-col">
                <h3>REDES SOCIALES</h3>
                <div class="social-icons" style="gap:15px; display:flex;">
                    <a href="#" class="text-muted hover-gold"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-muted hover-gold"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-muted hover-gold"><i class="fab fa-tiktok fa-lg"></i></a>
                </div>
            </div>
        </div>
        <div class="copyright text-center" style="margin-top:50px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.05);">
            <p class="text-muted" style="font-size:0.85rem;">&copy; <?php echo date('Y'); ?> THE BARBER SHOP. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- PREMIUM ANIMATION SCRIPTS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="assets/js/animations.js?v=<?php echo time(); ?>"></script>
</body>

</html>
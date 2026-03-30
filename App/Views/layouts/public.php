<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? \Core\Config::get('business.company_name'); ?></title>
    
    <!-- Meta Tags Dinámicos (Sprint 6.3) -->
    <meta name="description" content="<?php echo $meta_description ?? $description ?? 'Vanguardia tecnológica avanzada para la era de la inteligencia artificial. Transformamos complejidad en claridad estratégica.'; ?>">
    <meta name="keywords" content="<?php echo $meta_keywords ?? 'CRM, Inteligencia Artificial, Software, Consultoría IT'; ?>">
    <meta name="author" content="<?php echo \Core\Config::get('business.company_name'); ?> Team">

    <!-- Open Graph / Facebook (Sprint 6.4) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo url($_SERVER['REQUEST_URI'] ?? ''); ?>">
    <meta property="og:title" content="<?php echo $title ?? \Core\Config::get('business.company_name'); ?>">
    <meta property="og:description" content="<?php echo $meta_description ?? $description ?? 'Vanguardia tecnológica avanzada para la era de la inteligencia artificial.'; ?>">
    <meta property="og:image" content="<?php echo $meta_image ?? url('assets/images/og-image.jpg'); ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo url($_SERVER['REQUEST_URI'] ?? ''); ?>">
    <meta name="twitter:title" content="<?php echo $title ?? \Core\Config::get('business.company_name'); ?>">
    <meta name="twitter:description" content="<?php echo $meta_description ?? $description ?? 'Vanguardia tecnológica avanzada para la era de la inteligencia artificial.'; ?>">
    <meta name="twitter:image" content="<?php echo $meta_image ?? url('assets/images/og-image.jpg'); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="icon" type="image/x-icon" href="<?php echo url('assets/images/vezetaelea.ico'); ?>">
    <script>window.APP_URL = "<?php echo url(); ?>";</script>
    <link rel="stylesheet" href="<?php echo url('assets/css/variables.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/home.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/animations.css'); ?>">

</head>

<?php
// Global fetch for dynamic navigation
$db = \Core\Database::getInstance()->getConnection();
$navCategories = $db->query("SELECT name, slug FROM service_categories WHERE is_active = 1 ORDER BY order_position ASC")->fetchAll();
?>

<body>
    <!-- Legacy Preloader (Restored) -->
    <div id="vzl-loader" class="vzl-preloader">
        <div class="vzl-preloader-content">
            <div class="vzl-preloader-logo">
                <img src="<?php echo url('assets/images/logo.png'); ?>" alt="VeZetaeLeA"
                    style="height: 60px; width: auto; margin-bottom: 10px;">
            </div>
            <div class="vzl-preloader-spinner"></div>
        </div>
    </div>

    <script>
        (function () {
            // Comportamiento Legacy: Ocultar si venimos de navegación interna
            const ref = document.referrer;
            const isFromOutside = !ref || ref.includes('login') || ref.includes('home') || ref.split('/').pop() === 'app-crm' || ref.split('/').pop() === '';

            if (!isFromOutside) {
                const preloader = document.getElementById('vzl-loader');
                if (preloader) preloader.style.display = 'none';
            }
        })();
    </script>

    <header class="fixed-top w-100 py-3 glass-morphism border-bottom border-white-10">
        <!-- Chromatic animated border at bottom of navbar -->
        <div class="navbar-chromatic-border"></div>
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="<?php echo url(); ?>" class="text-decoration-none d-flex align-items-center">
                    <img src="<?php echo url('assets/images/logo.png'); ?>" alt="VeZetaeLeA Logo"
                        style="height: 50px; width: auto;">
                </a>
            </div>
            <nav class="d-none d-md-flex align-items-center gap-4">
                <div class="dropdown">
                    <a href="<?php echo url('#pilares'); ?>"
                        class="text-white text-decoration-none x-small transition-colors hover-gold d-flex align-items-center gap-1 dropdown-toggle tracking-widest"
                        id="servicesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Servicios
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark glass-morphism border-white-10 rounded-4 p-2 shadow-2xl mt-2"
                        aria-labelledby="servicesDropdown">
                        <?php foreach ($navCategories as $navCat): ?>
                            <li><a class="dropdown-item small text-white-50 hover-gold transition-all py-2 rounded-3"
                                    href="<?php echo url('service/category/' . $navCat['slug']); ?>"><?php echo $navCat['name']; ?></a>
                            </li>
                        <?php endforeach; ?>
                        <li>
                            <hr class="dropdown-divider border-white-10">
                        </li>
                        <li><a class="dropdown-item small text-primary fw-bold hover-white transition-all py-2 rounded-3"
                                href="<?php echo url('#pilares'); ?>">Ver Todos</a></li>
                    </ul>
                </div>
                <a href="<?php echo url('#kspace_premium'); ?>"
                    class="text-white text-decoration-none x-small transition-colors hover-gold tracking-widest">Productos</a>
                <a href="<?php echo url('blog'); ?>"
                    class="text-white text-decoration-none x-small transition-colors hover-gold tracking-widest">Blog</a>
                <a href="<?php echo url('ticket/request'); ?>"
                    class="text-white text-decoration-none x-small transition-colors hover-gold tracking-widest">Contacto</a>
            </nav>
            <div class="d-flex align-items-center gap-2 gap-md-4">
                <?php if (\Core\Auth::check()): ?>
                    <div class="d-flex align-items-center gap-2">
                         <span class="x-small text-white-50 fw-bold d-none d-md-block"><?php echo \Core\Config::get('business.company_name'); ?></span>
                        <a href="<?php echo url('dashboard'); ?>"
                            class="user-access-icon"
                            title="Ir al Dashboard">
                            <span class="material-symbols-outlined">dashboard</span>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?php echo url('auth/login'); ?>" class="user-access-icon" title="Acceso Clientes">
                        <span class="material-symbols-outlined">person</span>
                    </a>
                <?php endif; ?>

                <button id="mobile-toggle" class="d-md-none btn btn-link p-0 d-flex align-items-center justify-content-center border-0" 
                        type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                    <span class="material-symbols-outlined fs-1 vzl-text-gradient">menu</span>
                </button>
            </div>
        </div>
    </header>

    <div class="offcanvas offcanvas-end glass-morphism border-start border-white-10 text-white" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header border-bottom border-white-10 py-4">
            <div class="d-flex align-items-center gap-3">
                <img src="<?php echo url('assets/images/logo.png'); ?>" alt="Logo" style="height: 35px; width: auto;">
                <h5 class="offcanvas-title fw-bold vzl-text-gradient" id="mobileMenuLabel">Menú</h5>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="list-group list-group-flush">
                <a href="<?php echo url(); ?>" class="list-group-item list-group-item-action bg-transparent text-white border-white-5 py-3 px-4 d-flex align-items-center gap-3">
                    <span class="material-symbols-outlined text-primary">home</span> Inicio
                </a>
                
                <div class="list-group-item bg-transparent text-white border-white-5 p-0">
                    <a class="d-flex align-items-center justify-content-between py-3 px-4 text-decoration-none text-white w-100" data-bs-toggle="collapse" href="#mobileServices" role="button" aria-expanded="false">
                        <div class="d-flex align-items-center gap-3">
                            <span class="material-symbols-outlined text-primary">hub</span> Servicios
                        </div>
                        <span class="material-symbols-outlined x-small transition-transform">expand_more</span>
                    </a>
                    <div class="collapse bg-white-5" id="mobileServices">
                        <?php foreach ($navCategories as $navCat): ?>
                            <a href="<?php echo url('service/category/' . $navCat['slug']); ?>" class="d-block py-2 px-5 text-white-50 text-decoration-none small hover-gold">
                                <?php echo $navCat['name']; ?>
                            </a>
                        <?php endforeach; ?>
                        <a href="<?php echo url('#pilares'); ?>" class="d-block py-2 px-5 text-primary text-decoration-none small fw-bold">Ver Todos</a>
                    </div>
                </div>

                <a href="<?php echo url('#kspace_premium'); ?>" class="list-group-item list-group-item-action bg-transparent text-white border-white-5 py-3 px-4 d-flex align-items-center gap-3">
                    <span class="material-symbols-outlined text-primary">inventory_2</span> Productos
                </a>
                <a href="<?php echo url('blog'); ?>" class="list-group-item list-group-item-action bg-transparent text-white border-white-5 py-3 px-4 d-flex align-items-center gap-3">
                    <span class="material-symbols-outlined text-primary">rss_feed</span> Blog
                </a>
                <a href="<?php echo url('ticket/request'); ?>" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-3 px-4 d-flex align-items-center gap-3">
                    <span class="material-symbols-outlined text-primary">mail</span> Contacto
                </a>
            </div>
            
            <div class="mt-5 px-4">
                <a href="<?php echo url('auth/login'); ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2 shadow-gold text-dark">
                    <span class="material-symbols-outlined">login</span> Área de Clientes
                </a>
            </div>
        </div>
    </div>

    <main id="top">
        <?php echo $content; ?>
    </main>

    <div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 10000;"></div>

    <script>
        window.toast = function (message, type = 'primary', duration = 4000) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0 show mb-2 fade-in`;
            if (type === 'success') {
                toast.classList.add('vzl-animate-success');
            }
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined x-small">${type === 'danger' ? 'error' : (type === 'success' ? 'check_circle' : 'info')}</span>
                        <span class="small fw-500">${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;

            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            }, duration);
        }

        <?php if (\Core\Session::has('success')): ?>
            window.addEventListener('load', () => window.toast("<?php echo \Core\Session::flash('success'); ?>", 'success'));
        <?php endif; ?>
        <?php if (\Core\Session::has('error')): ?>
            window.addEventListener('load', () => window.toast("<?php echo \Core\Session::flash('error'); ?>", 'danger'));
        <?php endif; ?>
    </script>


    <footer class="bg-midnight border-top border-white-5 py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="<?php echo url('assets/images/logo.png'); ?>" alt="Logo"
                             style="height: 40px; width: auto;">
                        <h3 class="h5 mb-0 fw-bold"><span class="vzl-text-gradient"><?php echo mb_strtoupper(\Core\Config::get('business.company_name')); ?></span></h3>
                    </div>
                    <p class="text-white-50 small mb-4">Vanguardia tecnológica avanzada para la era de la inteligencia artificial. Transformamos complejidad en claridad estratégica.</p>
                    <div class="contact-info-footer small text-white-50">
                        <div class="mb-3"></div>
                        <p class="mb-2"><span class="me-2">📞</span> <?php echo \Core\Config::get('business.company_phone'); ?></p>
                        <p class="mb-2"><span class="me-2">✉️</span> <?php echo \Core\Config::get('business.company_mail'); ?></p>
                        <p class="mb-0"><span class="me-2">📍</span> <?php echo \Core\Config::get('business.company_address'); ?></p>
                    </div>
                </div>
                <div class="col-12 col-md-2 offset-md-1 mb-3 mb-md-0">
                    <h5 class="text-primary small fw-bold mb-4 tracking-widest">Servicios</h5>
                    <ul class="list-unstyled text-white-50 small mt-3">
                        <?php foreach ($navCategories as $navCat): ?>
                            <li class="mb-2"><a href="<?php echo url('service/category/' . $navCat['slug']); ?>"
                                    class="text-white-50 text-decoration-none hover-gold transition-colors"><?php echo $navCat['name']; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-12 col-md-2 mb-3 mb-md-0">
                    <h5 class="text-primary small fw-bold mb-4 tracking-widest">Navegación</h5>
                    <ul class="list-unstyled text-white-50 small mt-3">
                        <li class="mb-2"><a href="<?php echo url('#como-trabajamos'); ?>"
                                class="text-white-50 text-decoration-none hover-gold transition-colors">Cómo Trabajamos</a></li>
                        <li class="mb-2"><a href="<?php echo url('#kspace_premium'); ?>"
                                class="text-white-50 text-decoration-none hover-gold transition-colors">Productos</a></li>
                        <li class="mb-2"><a href="<?php echo url('blog'); ?>"
                                class="text-white-50 text-decoration-none hover-gold transition-colors">Blog</a></li>
                        <li class="mb-2"><a href="<?php echo url('ticket/request'); ?>"
                                class="text-white-50 text-decoration-none hover-gold transition-colors">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-12 col-md-2">
                    <h5 class="text-primary small fw-bold mb-4 tracking-widest">Portal de Clientes</h5>
                    <ul class="list-unstyled text-white-50 small mt-3">
                         <li class="mb-2"><a href="<?php echo url('auth/login'); ?>"
                                 class="text-white-50 text-decoration-none hover-gold transition-colors">Acceso</a></li>
                         <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-gold">Términos</a></li>
                         <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-gold">Privacidad</a></li>
                    </ul>
                </div>
    </div>

    <!-- Chromatic Kinetic Divider -->
    <div class="physichromie-divider"></div>
    
    <div class="footer-bottom-bar-clean">
        <div class="container d-flex justify-content-between align-items-center text-white-50 x-small position-relative" style="z-index: 3;">
            <p class="mb-0">© since <?php echo \Core\Config::get('business.company_est_year'); ?>. All Rights Reserved</p>
            <div class="d-flex gap-4 align-items-center">
                <!-- Social Links -->
                <!-- Instagram -->
                <a href="<?php echo \Core\Config::get('social.instagram'); ?>" target="_blank" class="text-white-50 hover-gold transition-all" title="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.282.11-.705.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                    </svg>
                </a>
                <!-- LinkedIn -->
                <a href="<?php echo \Core\Config::get('social.linkedin'); ?>" target="_blank" class="text-white-50 hover-gold transition-all" title="LinkedIn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/>
                    </svg>
                </a>
                <!-- GitHub -->
                <a href="<?php echo \Core\Config::get('social.github'); ?>" target="_blank" class="text-white-50 hover-gold transition-all" title="GitHub">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8"/>
                    </svg>
                </a>
                <!-- Twitter / X -->
                <a href="<?php echo \Core\Config::get('social.twitter'); ?>" target="_blank" class="text-white-50 hover-gold transition-all" title="Twitter / X">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
                    </svg>
                </a>
                <!-- Facebook -->
                <a href="<?php echo \Core\Config::get('social.facebook'); ?>" target="_blank" class="text-white-50 hover-gold transition-all" title="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</footer>

    <!-- Scroll to Top -->
    <div id="scroll-to-top" class="scroll-to-top">
        <span class="material-symbols-outlined">expand_less</span>
    </div>

    <!-- Floating WhatsApp -->
    <a href="https://wa.me/<?php echo str_replace([' ', '+', '-'], '', \Core\Config::get('business.company_phone')); ?>?text=Hola!%20Vengo%20desde%20la%20web%20y%20quisiera%20consultar%20sobre%20sus%20servicios." class="floating-whatsapp" target="_blank" title="Hablar con un consultor">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.94 3.659 1.437 5.634 1.437h.005c6.551 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
        </svg>
    </a>

    <script src="<?php echo url('assets/js/guided_flow.js'); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
             // Legacy Preloader Removal Logic
             const removePreloader = () => {
                 const loader = document.getElementById('vzl-loader');
                 if (loader && loader.style.display !== 'none') {
                     setTimeout(() => {
                         loader.classList.add('fade-out');
                         setTimeout(() => loader.remove(), 500);
                     }, 800);
                 }
             };
             
             if (document.readyState === 'complete') {
                 removePreloader();
             } else {
                 window.addEventListener('load', removePreloader);
             }

             // Scroll Button
            const scrollBtn = document.getElementById('scroll-to-top');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 400) scrollBtn.style.display = 'flex';
                else scrollBtn.style.display = 'none';
            });
            scrollBtn.addEventListener('click', () => window.scrollTo({top:0, behavior:'smooth'}));
        });
    </script>
</body>
</html>
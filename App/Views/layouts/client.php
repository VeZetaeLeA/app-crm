<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $title; ?>
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fuentes: URL configurable desde .env vía FONT_URL -->
    <link href="<?php echo \Core\Config::get('typography.font_url'); ?>" rel="stylesheet">
    <!-- Design System CSS -->
    <link rel="stylesheet" href="<?php echo url('assets/css/variables.css?v=1.0.8'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css?v=1.0.8'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/animations.css?v=1.0.8'); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo url('assets/images/VeZetaeLeA.ico'); ?>">
    <!-- ✦ Dynamic Design Tokens — injected from .env via Config (Zero Hardcode) -->
    <?php $ui = \Core\Config::get('ui'); $typo = \Core\Config::get('typography'); ?>
    <style>
        :root {
            --vzl-color-bg-dark:       <?= \Core\Config::get('ui.color_bg_dark') ?>;
            --vzl-color-surface-dark:  <?= \Core\Config::get('ui.color_surface_dark') ?>;
            --vzl-color-surface-elev:  <?= \Core\Config::get('ui.color_surface_elev') ?>;
            --vzl-color-border-dark:   <?= \Core\Config::get('ui.color_border_dark') ?>;
            --vzl-color-bg-light:      <?= \Core\Config::get('ui.color_bg_light') ?>;
            --vzl-color-surface-light: <?= \Core\Config::get('ui.color_surface_light') ?>;
            --vzl-color-border-light:  <?= \Core\Config::get('ui.color_border_light') ?>;
            --vzl-primary:    <?= \Core\Config::get('ui.primary_color') ?>;
            --vzl-secondary:  <?= \Core\Config::get('ui.secondary_color') ?>;
            --vzl-color-gold: <?= \Core\Config::get('ui.gold_color') ?>;
            --vzl-color-success: <?= \Core\Config::get('ui.color_success') ?>;
            --vzl-color-warning: <?= \Core\Config::get('ui.color_warning') ?>;
            --vzl-color-danger:  <?= \Core\Config::get('ui.color_danger') ?>;
            --vzl-color-info:    <?= \Core\Config::get('ui.color_info') ?>;
            --vzl-ui-radius-sm:   <?= \Core\Config::get('ui.radius_sm') ?>;
            --vzl-ui-radius-md:   <?= \Core\Config::get('ui.radius_md') ?>;
            --vzl-ui-radius-lg:   <?= \Core\Config::get('ui.radius_lg') ?>;
            --vzl-ui-radius-pill: <?= \Core\Config::get('ui.radius_pill') ?>;
            --vzl-font-heading:   <?= \Core\Config::get('typography.font_heading') ?>;
            --vzl-font-body:      <?= \Core\Config::get('typography.font_body') ?>;
            --vzl-font-mono:      <?= \Core\Config::get('typography.font_mono') ?>;
        }
    </style>
    <script>window.APP_URL = "<?php echo url(); ?>";</script>
</head>

<body class="bg-deep-black" id="app-body">
    <div class="d-flex min-vh-100">
        <!-- Sidebar Overlay (mobile) -->
        <div id="sidebar-overlay" class="position-fixed w-100 h-100 bg-black bg-opacity-50 d-none"
            style="z-index: 1040; top: 0; left: 0;"></div>

        <!-- Sidebar -->
        <aside id="main-sidebar"
            class="bg-midnight border-end border-white-10 flex-shrink-0 d-flex flex-column sidebar-responsive">
            <div class="p-4 border-bottom border-white-10 bg-deep-black bg-opacity-50 side-header d-flex align-items-center justify-content-between">
                <a href="<?php echo url(); ?>"
                    class="text-decoration-none d-flex align-items-center gap-3 side-logo-link">
                    <img src="<?php echo url('assets/images/logo.png'); ?>" alt="Logo"
                        class="side-logo-img"
                        style="width: 38px; height: 38px; object-fit: contain;">

                    <h2 class="text-white h5 mb-0 fw-bold side-logo-text" style="font-family: var(--vzl-font-body); font-size: 1.1rem; letter-spacing: -0.02em;">
                        <span class="vzl-text-gradient fw-black"><?php echo mb_strtoupper(\Core\Config::get('business.company_name', 'Tu Empresa')); ?></span>
                    </h2>
                </a>
                <button id="close-sidebar" class="btn text-white-50 d-lg-none p-0 border-0">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <nav class="flex-grow-1 p-3 mt-2 overflow-y-auto">
                <p class="text-white-50 x-small fw-bold uppercase px-3 mb-2 tracking-widest"><?= __('sidebar.main_menu') ?></p>

                <a href="<?php echo url('dashboard'); ?>"
                    class="nav-link-custom mb-1 <?php echo ($_SERVER['REQUEST_URI'] == url('dashboard') || $_SERVER['REQUEST_URI'] == '/' || strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">dashboard</span>
                    <?= __('sidebar.dashboard') ?>
                </a>

                <?php if (\Core\Auth::isClient()): ?>
                    <a href="<?php echo url('ticket'); ?>"
                        class="nav-link-custom mb-1 <?php echo ($_SERVER['REQUEST_URI'] == url('ticket') || strpos($_SERVER['REQUEST_URI'], '/ticket') !== false) ? 'active' : ''; ?>">
                        <span class="material-symbols-outlined">confirmation_number</span>
                        <?= __('sidebar.my_tickets') ?>
                    </a>

                    <?php $activeServices = getActiveServices(); ?>
                    <?php if (!empty($activeServices)): ?>
                        <p class="text-white-50 x-small fw-bold uppercase px-3 mt-4 mb-2 tracking-widest"><?= __('sidebar.active_services') ?></p>
                        <?php foreach ($activeServices as $as): ?>
                            <a href="<?php echo url('project/manage/' . $as['id']); ?>"
                                class="nav-link-custom mb-1 <?php echo strpos($_SERVER['REQUEST_URI'], '/project/manage/' . $as['id']) !== false ? 'active' : ''; ?>">
                                <span class="material-symbols-outlined">rocket_launch</span>
                                <?php echo $as['name']; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <a href="<?php echo url('project/workspace'); ?>"
                        class="nav-link-custom mt-3 mb-1 <?php echo strpos($_SERVER['REQUEST_URI'], '/project/workspace') !== false ? 'active' : ''; ?>">
                        <span class="material-symbols-outlined">folder_shared</span>
                        <?= __('sidebar.global_workspace') ?>
                    </a>
                    <a href="<?php echo url('invoice'); ?>"
                        class="nav-link-custom mb-1 <?php echo strpos($_SERVER['REQUEST_URI'], '/invoice') !== false ? 'active' : ''; ?>">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <?= __('sidebar.my_invoices') ?>
                    </a>
                <?php endif; ?>

                <?php if (\Core\Auth::isAdmin() || \Core\Auth::isStaff()): ?>
                    <a href="<?php echo url('dashboard'); ?>" class="nav-link-custom mb-1 active">
                        <span class="material-symbols-outlined">support_agent</span>
                        <?= __('sidebar.ticket_management') ?>
                    </a>
                    <a href="<?php echo url('project/workspace'); ?>"
                        class="nav-link-custom mb-1 <?php echo strpos($_SERVER['REQUEST_URI'], '/project/workspace') !== false ? 'active' : ''; ?>">
                        <span class="material-symbols-outlined">folder_shared</span>
                        <?= __('sidebar.project_workspace') ?>
                    </a>
                    <a href="#" class="nav-link-custom mb-1">
                        <span class="material-symbols-outlined">group</span>
                        <?= __('sidebar.users_roles') ?>
                    </a>
                <?php endif; ?>

                <?php if (\Core\Auth::isAdmin()): ?>
                    <p class="text-white-50 x-small fw-bold uppercase px-3 mt-4 mb-2 tracking-widest"><?= __('sidebar.administration') ?></p>
                    <a href="<?php echo url('admin/services'); ?>"
                        class="nav-link-custom mb-1 <?php echo strpos($_SERVER['REQUEST_URI'], 'admin/services') !== false ? 'active' : ''; ?>">
                        <span class="material-symbols-outlined">settings_suggest</span>
                        <?= __('sidebar.services_cms') ?>
                    </a>
                    <a href="<?php echo url('admin/blog'); ?>"
                        class="nav-link-custom mb-1 <?php echo strpos($_SERVER['REQUEST_URI'], 'admin/blog') !== false ? 'active' : ''; ?>">
                        <span class="material-symbols-outlined">newspaper</span>
                        <?= __('sidebar.blog_management') ?>
                    </a>
                    <a href="<?php echo url('admin/users'); ?>"
                        class="nav-link-custom mb-1 <?php echo strpos($_SERVER['REQUEST_URI'], 'admin/users') !== false ? 'active' : ''; ?>">
                        <span class="material-symbols-outlined">shield_person</span>
                        <?= __('sidebar.users_roles') ?>
                    </a>
                <?php endif; ?>

                <div class="mt-auto p-3">
                    <a href="<?php echo url(); ?>"
                        class="btn btn-outline-primary btn-sm w-100 py-3 rounded-4 fw-bold uppercase tracking-widest d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined fs-5">open_in_new</span> <?= __('sidebar.view_public_site') ?>
                    </a>
                </div>
            </nav>

            <div class="p-3 border-top border-white-10">
                <div class="user-card glass-morphism p-3 rounded-4 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-midnight d-flex align-items-center justify-content-center text-accent fw-bold"
                        style="width: 40px; height: 40px;">
                        <?php echo strtoupper(substr(\Core\Auth::user()['name'], 0, 1)); ?>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-white small fw-bold mb-0 text-truncate">
                            <?php echo \Core\Auth::user()['name']; ?>
                        </p>
                        <p class="text-white-50 x-small mb-0 text-truncate uppercase">
                            <?php echo \Core\Auth::role(); ?>
                        </p>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <a href="<?php echo url('profile/settings'); ?>" class="text-white-50 hover-gold transition-all"
                            title="<?= __('sidebar.profile_settings') ?>">
                            <span class="material-symbols-outlined fs-5">settings</span>
                        </a>
                        <a href="<?php echo url('auth/logout'); ?>" class="text-white-50 hover-gold transition-all"
                            title="<?= __('sidebar.logout') ?>">
                            <span class="material-symbols-outlined fs-5">logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow-1 overflow-auto bg-deep-black" style="max-height: 100vh;">
            <header
                class="bg-midnight bg-opacity-75 border-bottom border-white-10 sticky-top p-3 px-md-4 backdrop-blur">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <button id="toggle-sidebar" class="btn text-white p-0 border-0 d-lg-none">
                            <span class="material-symbols-outlined fs-2">menu</span>
                        </button>
                        <h1 class="h5 text-white fw-bold mb-0">
                            <?php echo $title; ?>
                        </h1>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-md-3">
                        <button id="theme-toggle-btn" type="button"
                            class="btn btn-outline-light btn-sm rounded-circle p-2 border-white-10 d-flex align-items-center justify-content-center"
                            title="<?= __('common.theme_toggle') ?>">
                            <span class="material-symbols-outlined fs-5" id="theme-icon">light_mode</span>
                        </button>
                        <div class="dropdown">
                            <button id="notification-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="btn btn-outline-light btn-sm rounded-circle p-2 border-white-10 d-flex align-items-center justify-content-center position-relative">
                                <span class="material-symbols-outlined fs-5">notifications</span>
                                <span id="notification-badge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-midnight d-none"
                                    style="padding: 0.35em;"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-white-10 glass-morphism p-0 overflow-hidden"
                                style="width: 320px; max-height: 400px; overflow-y: auto !important; margin-top: 10px;">
                                <div
                                    class="p-3 border-bottom border-white-10 d-flex justify-content-between align-items-center bg-midnight">
                                    <h6 class="mb-0 fw-bold text-white small uppercase tracking-widest"><?= __('header.notifications') ?>
                                    </h6>
                                    <button
                                        class="btn btn-link py-0 px-2 text-primary x-small text-decoration-none fw-bold"
                                        id="mark-read-btn"><?= __('header.mark_as_read') ?></button>
                                </div>
                                <div id="notification-dropdown-items" class="d-flex flex-column">
                                    <div class="p-4 text-center text-white-50 small placeholder-text"><?= __('header.no_notifications') ?></div>
                                </div>
                            </ul>
                        </div>
                        <div class="bg-white-10 d-none d-md-block" style="width: 1px; height: 24px;"></div>
                        <span class="text-white-50 small fw-bold d-none d-md-inline">
                            <?php echo date('d M, Y'); ?>
                        </span>
                    </div>
                </div>
            </header>

            <div class="p-3 pt-lg-1 p-lg-5 mb-5 mb-lg-0">
                <?php echo $content; ?>
            </div>

            <!-- Quick Action Bar (Mobile Only) -->
            <div class="d-lg-none position-fixed bottom-0 start-0 w-100 bg-midnight border-top border-white-10 backdrop-blur d-flex justify-content-around py-2 px-1 no-print"
                style="z-index: 1030; height: 65px;">
                <a href="<?php echo url('dashboard'); ?>"
                    class="d-flex flex-column align-items-center text-decoration-none <?php echo strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'text-primary' : 'text-white-50'; ?>">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="x-small fw-bold">Inicio</span>
                </a>
                <a href="<?php echo url('ticket'); ?>"
                    class="d-flex flex-column align-items-center text-decoration-none <?php echo strpos($_SERVER['REQUEST_URI'], 'ticket') !== false ? 'text-primary' : 'text-white-50'; ?>">
                    <span class="material-symbols-outlined">confirmation_number</span>
                    <span class="x-small fw-bold">Tickets</span>
                </a>
                <a href="<?php echo url('invoice'); ?>"
                    class="d-flex flex-column align-items-center text-decoration-none <?php echo strpos($_SERVER['REQUEST_URI'], 'invoice') !== false ? 'text-primary' : 'text-white-50'; ?>">
                    <span class="material-symbols-outlined">receipt_long</span>
                    <span class="x-small fw-bold">Facturas</span>
                </a>
                <a href="<?php echo url('profile/settings'); ?>"
                    class="d-flex flex-column align-items-center text-decoration-none <?php echo strpos($_SERVER['REQUEST_URI'], 'settings') !== false ? 'text-primary' : 'text-white-50'; ?>">
                    <span class="material-symbols-outlined">settings</span>
                    <span class="x-small fw-bold">Perfil</span>
                </a>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar-responsive {
            width: 280px;
            transition: transform 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .sidebar-responsive {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1050;
                transform: translateX(-100%);
            }

            .sidebar-responsive.active {
                transform: translateX(0);
            }

            #main-sidebar {
                overflow-y: auto !important;
                max-height: 100vh;
                padding-bottom: 80px !important;
            }

            .side-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .side-logo-link {
                flex-direction: column;
                gap: 8px !important;
                width: 100%;
                text-align: center;
            }

            .side-logo-img {
                width: 45px !important;
                height: 45px !important;
            }

            .side-logo-text {
                font-size: 1rem !important;
            }
        }

        /* Responsive Table adjustments */
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: var(--vzl-color-gold) var(--vzl-color-bg-dark);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-sidebar');
            const closeBtn = document.getElementById('close-sidebar');
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            function toggleSidebar() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('d-none');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }

            if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
            if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
            if (overlay) overlay.addEventListener('click', toggleSidebar);

            // --- Theme Toggle Logic ---
            const themeBtn = document.getElementById('theme-toggle-btn');
            const themeIcon = document.getElementById('theme-icon');
            const htmlEl = document.documentElement;
            const bodyEl = document.getElementById('app-body');

            function applyTheme(theme) {
                htmlEl.setAttribute('data-theme', theme);
                if (bodyEl) bodyEl.setAttribute('data-theme', theme);
                updateThemeIcon(theme);
            }

            const savedTheme = localStorage.getItem('Vezetaelea-theme') || 'dark';
            applyTheme(savedTheme);

            if (themeBtn) {
                themeBtn.addEventListener('click', () => {
                    const currentTheme = htmlEl.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('Vezetaelea-theme', newTheme);
                    applyTheme(newTheme);
                });
            }

            function updateThemeIcon(theme) {
                if (themeIcon) {
                    themeIcon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
                }
            }

            // --- Flash Message Toast (visible on all devices) ---
            function showToast(message, type) {
                const colors = { success: '#198754', danger: '#dc3545', info: '#0dcaf0' };
                const icons = { success: 'check_circle', danger: 'error', info: 'info' };
                const toast = document.createElement('div');
                toast.style.cssText = `position:fixed;bottom:80px;left:50%;transform:translateX(-50%) translateY(0);z-index:9999;min-width:280px;max-width:90vw;display:flex;align-items:center;gap:10px;padding:14px 20px;border-radius:12px;background:${colors[type] || colors.info};color:white;font-weight:700;font-size:0.85rem;box-shadow:0 8px 30px rgba(0,0,0,0.4);transition:opacity 0.5s ease;`;
                toast.innerHTML = `<span class="material-symbols-outlined" style="font-size:1.2rem">${icons[type] || icons.info}</span> ${message}`;
                document.body.appendChild(toast);
                setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 500); }, 4000);
            }
            <?php if ($msg = \Core\Session::flash('success')): ?>
                showToast("<?php echo addslashes($msg); ?>", 'success');
            <?php endif; ?>
            <?php if ($msg = \Core\Session::flash('error')): ?>
                showToast("<?php echo addslashes($msg); ?>", 'danger');
            <?php endif; ?>

            // --- Skeleton Management ---
            setTimeout(() => {
                document.querySelectorAll('.skeleton-loader, .skeleton-row').forEach(el => {
                    el.style.transition = 'opacity 0.4s ease';
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 400);
                });
            }, 600);
        });
    </script>
    <script src="<?php echo url('assets/js/notifications.js?v=1.0.8'); ?>"></script>
    <script src="<?php echo url('assets/js/realtime.js?v=1.0.8'); ?>"></script>
</body>

</html>
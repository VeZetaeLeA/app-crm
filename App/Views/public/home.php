<?php
// app/views/public/home.php
?>



<!-- Hero Section (VeZetaeLeA — Clean & Focused) -->
<header class="hero position-relative vh-100 d-flex align-items-center justify-content-center overflow-hidden">
    <video autoplay muted loop playsinline class="hero-video position-absolute w-100 h-100" style="object-fit: cover; z-index: 1; opacity: 0.35; transform-origin: center center;">
        <source src="<?= url('assets/images/VeZetaeLeA_home_video.mp4') ?>" type="video/mp4">
    </video>
    <!-- Overlay más opaco para reducir ruido visual del video -->
    <div class="position-absolute w-100 h-100 bg-deep-black" style="z-index: 2; opacity: 0.75;"></div>
    <!-- Gradiente sutil desde abajo para anclar el contenido -->
    <div class="position-absolute w-100 h-100" style="z-index: 2; background: linear-gradient(to top, var(--vzl-deep-black) 0%, transparent 60%);"></div>

    <div class="container text-center position-relative" style="z-index: 3;">
        <h1 class="hero-title fw-black text-white mb-4 tracking-tighter">
            <?= __('home.hero.title') ?>
        </h1>

        <p class="lead text-white-50 mx-auto mb-5 max-w-800">
            <?= __('home.hero.subtitle') ?>
        </p>

        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
            <a href="#pilares" class="btn btn-outline-white px-5 py-3 small fw-bold uppercase rounded-pill transition-all"><?= __('home.hero.cta_primary') ?></a>
            <a href="#contacto" class="btn vzl-btn-glow-magenta px-5 py-3 fw-bold uppercase tracking-widest rounded-pill transition-all"><?= __('home.hero.cta_secondary') ?></a>
        </div>
    </div>
</header>

<!-- Social Proof Strip (separado del hero para respirabilidad) -->
<div class="vzl-social-proof bg-midnight border-top border-bottom border-white-5 py-4">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-center align-items-center gap-4 gap-md-5">
            <div class="d-flex align-items-center gap-3">
                <span class="vzl-text-gradient-vibrant fs-2 fw-black"><span class="vzl-counter-display" data-target="<?php echo \Core\Config::get('business.years_exp', '10'); ?>">0</span>+</span>
                <span class="text-white-50 small fw-bold uppercase tracking-widest"><?= __('home.stats.years') ?></span>
            </div>
            <div class="d-none d-md-block" style="width: 1px; height: 40px; background: rgba(255,255,255,0.08);"></div>
            <div class="d-flex align-items-center gap-3">
                <span class="vzl-text-gradient-vibrant fs-2 fw-black"><span class="vzl-counter-display" data-target="85000">0</span>+</span>
                <span class="text-white-50 small fw-bold uppercase tracking-widest"><?= __('home.stats.projects') ?></span>
            </div>
            <div class="d-none d-md-block" style="width: 1px; height: 40px; background: rgba(255,255,255,0.08);"></div>
            <div class="d-flex align-items-center gap-3">
                <span class="vzl-text-gradient-vibrant fs-2 fw-black"><span class="vzl-counter-display" data-target="4">0</span></span>
                <span class="text-white-50 small fw-bold uppercase tracking-widest"><?= __('home.stats.verticals') ?></span>
            </div>
            <div class="d-none d-md-block" style="width: 1px; height: 40px; background: rgba(255,255,255,0.08);"></div>
            <div class="d-flex align-items-center gap-3">
                <span class="vzl-text-gradient-vibrant fs-2 fw-black"><span class="vzl-counter-display" data-target="1">0</span></span>
                <span class="text-white-50 small fw-bold uppercase tracking-widest"><?= __('home.stats.product') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Diferenciación Estratégica (VeZetaeLeA Refined) -->
<section id="por-que-nosotros" class="bg-midnight border-top border-white-5 py-5 overflow-hidden cta-parallax-bg" style="padding: 7rem 0 !important;">
    <div class="container py-5 position-relative" style="z-index: 2;">
        <!-- Cabecera de Sección a Ancho Completo -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="vzl-section-header text-start">
                    <h6 class="vzl-section-subtitle">Nuestro Factor Tech-Estratégico</h6>
                    <h2 class="vzl-section-title">Diseñamos ecosistemas. <span class="vzl-text-gradient-vibrant">No herramientas aisladas.</span></h2>
                </div>
            </div>
        </div>

        <div class="row align-items-stretch g-5">
            <!-- Pilares de Valor -->
            <div class="col-lg-6">
                <div class="vzl-feature-grid mt-2">
                    <!-- Pilar 1: Data Engineering -->
                    <div class="vzl-feature-card mb-4">
                        <div class="d-flex gap-4">
                            <div class="vzl-icon-container">
                                <div class="vzl-icon-glow bg-primary"></div>
                                <div class="vzl-icon-box text-primary">
                                    <span class="material-symbols-outlined fs-1">database</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="vzl-card-title text-white">Sistema Operativo de Datos</h4>
                                <p class="vzl-card-text mb-0">No solo gestionamos datos, los convertimos en tu arma competitiva. ETL Pipelines, Data Warehousing, Gobierno de Datos y BI en un solo ecosistema integrado que escala con tu empresa.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Pilar 2: Software Premium -->
                    <div class="vzl-feature-card mb-4">
                        <div class="d-flex gap-4">
                            <div class="vzl-icon-container">
                                <div class="vzl-icon-glow bg-primary"></div>
                                <div class="vzl-icon-box text-primary">
                                    <span class="material-symbols-outlined fs-1">code</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="vzl-card-title text-white">Ingeniería de Software Premium</h4>
                                <p class="vzl-card-text mb-0">CRMs a medida, Web Apps de alta conversión, Sistemas complejos y nuestro propio <strong class="vzl-text-gradient-vibrant">App-CRM</strong>. Cada línea de código optimizada para el rendimiento extremo y la escalabilidad real.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Pilar 3: AI & Automation -->
                    <div class="vzl-feature-card">
                        <div class="d-flex gap-4">
                            <div class="vzl-icon-container">
                                <div class="vzl-icon-glow" style="background: var(--vzl-gold);"></div>
                                <div class="vzl-icon-box" style="color: var(--vzl-gold);">
                                    <span class="material-symbols-outlined fs-1">smart_toy</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="vzl-card-title text-white">Inteligencia como Servicio</h4>
                                <p class="vzl-card-text mb-0">Desde dashboards ejecutivos con Power BI y Looker Studio hasta modelos predictivos con Machine Learning y Agentes de IA. Tomás decisiones basadas en datos reales, no en intuición.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Imagen Lateral Sincronizada -->
            <div class="col-lg-6">
                <div class="position-relative h-100 d-flex flex-column">
                    <div class="vzl-image-glow position-absolute top-50 start-50 translate-middle w-100 h-100 bg-primary opacity-10 blur-3xl" style="border-radius: 50%;"></div>
                    <div class="rounded-5 overflow-hidden border border-white-10 shadow-2xl position-relative flex-grow-1">
                        <img src="<?= url('assets/images/vezetaelea_working.png') ?>" alt="<?= \Core\Config::get('business.company_name') ?> — Ingeniería Estratégica" class="img-fluid w-100 h-100" style="object-fit: cover; object-position: center;">
                        <div class="position-absolute bottom-0 start-0 w-100 p-4 bg-gradient-to-t from-midnight to-transparent">
                            <!-- Tech Badges -->
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="vzl-tech-badge">
                                    <span class="material-symbols-outlined text-primary fs-5">verified</span>
                                    <span class="text-white fw-bold xx-small uppercase tracking-widest">Excelencia Certificada</span>
                                </div>
                                <div class="vzl-tech-badge">
                                    <span class="material-symbols-outlined text-warning fs-5">stars</span>
                                    <span class="text-white fw-bold xx-small uppercase tracking-widest">Satisfacción en cada Sprint</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Excelencia Técnica Section (Nuestros Pilares) -->
<section id="pilares" class="bg-midnight border-top border-white-5 py-5">
    <div class="container py-5">
        <div class="vzl-section-header">
            <h6 class="vzl-section-subtitle">Excelencia Técnica</h6>
            <h2 class="vzl-section-title">Nuestros <span class="vzl-text-gradient-vibrant">Pilares</span></h2>
        </div>

        <div class="row g-4 mt-4">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-white-10 bg-deep-black p-0 overflow-hidden hover-lift transition-all rounded-5">
                        <?php if (!empty($category['image'])): ?>
                            <div class="position-relative" style="height: 140px; overflow: hidden;">
                                <div class="position-absolute w-100 h-100 bg-gradient-to-t from-deep-black via-transparent to-transparent opacity-90" style="z-index: 2;"></div>
                                <img src="<?= url($category['image']); ?>" class="w-100 h-100 object-fit-cover transition-all" alt="<?= $category['name']; ?>" style="object-fit: cover;">
                                
                                <div class="position-absolute bottom-0 start-0 p-4" style="z-index: 3; margin-bottom: -25px;">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 bg-midnight-soft text-primary shadow-gold border border-white-10" style="width: 50px; height: 50px;">
                                        <span class="material-symbols-outlined fs-2"><?= $category['icon']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 pt-5 d-flex flex-column h-100">
                        <?php else: ?>
                            <div class="p-4 d-flex flex-column h-100">
                                <div class="mb-4 d-inline-flex align-items-center justify-content-center rounded-4 bg-white-5 text-primary shadow-gold" style="width: 50px; height: 50px;">
                                    <span class="material-symbols-outlined fs-2"><?= $category['icon']; ?></span>
                                </div>
                        <?php endif; ?>
                            <h3 class="h6 text-white fw-bold mb-3 uppercase tracking-widest"><?= $category['name']; ?></h3>
                            <p class="text-white-50 x-small mb-4 flex-grow-1"><?= $category['description']; ?></p>
                            <a href="<?= url('service/category/' . $category['slug']); ?>" class="text-primary text-decoration-none fw-bold small text-uppercase tracking-widest d-flex align-items-center gap-2 mt-auto">
                                Saber Más <span class="material-symbols-outlined fs-6">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Modelo de Entrega -->
<section id="como-trabajamos" class="bg-deep-black border-top border-white-5 py-5 cta-parallax-bg" style="padding: 7rem 0 !important;">
    <div class="container py-5">
        <div class="glass-morphism rounded-5 p-5 text-center border-white-10 shadow-2xl">
            <div class="vzl-section-header">
                <h6 class="vzl-section-subtitle">Modelo de Entrega</h6>
                <h2 class="vzl-section-title">Nuestro <span class="vzl-text-gradient-vibrant">Modelo de Trabajo</span></h2>
            </div>
            <p class="text-white-50 mx-auto mb-5" style="max-width: 600px;">Desarrollo preciso y arquitecturas escalables que garantizan impacto medible en tu negocio.</p>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="process-card p-4 rounded-5 border-white-10 h-100 bg-midnight hover-lift transition-all text-center">
                        <span class="material-symbols-outlined fs-1 text-primary mb-3">search</span>
                        <h4 class="text-white h6 fw-bold mb-2">Diagnóstico</h4>
                        <p class="text-white-50 x-small mb-0">Análisis detallado de tu operativa y definición de objetivos.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="process-card p-4 rounded-5 border-white-10 h-100 bg-midnight hover-lift transition-all text-center">
                        <span class="material-symbols-outlined fs-1 text-primary mb-3">architecture</span>
                        <h4 class="text-white h6 fw-bold mb-2">Arquitectura</h4>
                        <p class="text-white-50 x-small mb-0">Diseño técnico escalable adaptado a tu infraestructura.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="process-card p-4 rounded-5 border-white-10 h-100 bg-midnight hover-lift transition-all text-center">
                        <span class="material-symbols-outlined fs-1 text-primary mb-3">bolt</span>
                        <h4 class="text-white h6 fw-bold mb-2">Ejecución</h4>
                        <p class="text-white-50 x-small mb-0">Lanzamientos ágiles con impacto tangible desde el inicio.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="process-card p-4 rounded-5 border-white-10 h-100 bg-midnight hover-lift transition-all text-center">
                        <span class="material-symbols-outlined fs-1 text-primary mb-3">trending_up</span>
                        <h4 class="text-white h6 fw-bold mb-2">Evolución</h4>
                        <p class="text-white-50 x-small mb-0">Soporte continuo y optimización de rendimiento.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Tech Stack -->
<?php
$stackDir = 'assets/images/stack/';
$stackItems = [];
if (is_dir(public_path($stackDir))) {
    $files = scandir(public_path($stackDir));
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && (strpos($file, '.png') !== false || strpos($file, '.jpg') !== false || strpos($file, '.svg') !== false)) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $stackItems[] = ['logo' => url($stackDir . $file), 'name' => $name];
        }
    }
}
?>

<?php if (!empty($stackItems)): ?>
<section class="tech-stack-section bg-deep-black border-top border-white-5 py-5 overflow-hidden">
    <div class="vzl-section-header mb-5">
        <h6 class="vzl-section-subtitle">Herramientas de nivel Enterprise</h6>
        <h2 class="vzl-section-title">Nuestro stack <span class="vzl-text-gradient-vibrant">tecnológico</span></h2>
    </div>
    <div class="tech-ticker-wrapper overflow-hidden pb-4 d-flex">
        <!-- Render 4 duplicate tracks to ensure enough width to smoothly loop without jumping -->
        <?php for($loopTrack=0; $loopTrack<4; $loopTrack++): ?>
            <div class="tech-ticker-content d-flex align-items-center gap-4 pe-4 ms-0">
                <?php foreach ($stackItems as $item): ?>
                    <div class="tech-item d-flex flex-column align-items-center gap-2" style="min-width: 90px;">
                        <img src="<?= $item['logo'] ?>" alt="<?= $item['name'] ?>" style="height: 35px; width: auto; filter: grayscale(1) opacity(0.6);">
                        <span class="text-white-50 xx-small fw-bold uppercase tracking-tighter d-block text-center"><?= $item['name'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endfor; ?>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     🆕 VeZetaeLeA App-CRM — VERSIÓN EXPANDIDA (para comparar con la anterior)
     Para mantener esta versión: eliminar la sección anterior (#vzl-os-showcase)
     Para mantener la anterior: eliminar esta sección
     ============================================================ -->
<section id="kspace_premium" class="bg-deep-black border-top border-white-5 py-5 cta-parallax-bg" style="padding: 7rem 0 !important;">
    <div class="container py-5">

        <!-- Badge + Header -->
        <div class="vzl-section-header text-center mb-5">
            <div class="d-inline-flex align-items-center gap-2 mb-3 px-4 py-2 rounded-pill border border-warning bg-warning-subtle">
                <span class="material-symbols-outlined text-warning fs-6">workspace_premium</span>
                <span class="x-small text-warning fw-bold uppercase tracking-widest">APP-CRM es Exclusivo de <?= \Core\Config::get('business.company_name') ?></span>
            </div>
            <h6 class="vzl-section-subtitle">Control Maestro</h6>
            <h2 class="vzl-section-title">Te ofrecemos un <span class="vzl-text-gradient-vibrant">Sistema Operativo Propio</span></h2>
            <p class="text-white-50 mx-auto mt-3" style="max-width: 680px;">
                App-CRM es nuestra plataforma enterprise construida con los más altos estándares de la industria. La misma herramienta que usamos para gestionar nuestros clientes, ahora disponible para tu empresa.
            </p>
        </div>

        <div class="row g-5 align-items-center">
            <!-- Left: Carousel Images -->
            <div class="col-lg-6">
                <div class="carousel slide carousel-fade glass-morphism p-3 rounded-5 border border-white-10 shadow-2xl overflow-hidden" data-bs-ride="carousel" data-bs-interval="3000">
                    <div class="carousel-inner rounded-4">
                        <div class="carousel-item active">
                            <img src="<?= url('assets/images/vzl_os_crm.png'); ?>" class="d-block w-100" style="height: 380px; object-fit: cover;" alt="CRM Dashboard">
                            <div class="position-absolute bottom-0 start-0 w-100 px-4 py-3 glass-morphism">
                                <span class="x-small text-white fw-bold uppercase tracking-widest">📊 Dashboard Interactivo</span>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="<?= url('assets/images/vzl_os_ai.png'); ?>" class="d-block w-100" style="height: 380px; object-fit: cover;" alt="AI Module">
                            <div class="position-absolute bottom-0 start-0 w-100 px-4 py-3 glass-morphism">
                                <span class="x-small text-white fw-bold uppercase tracking-widest">🤖 Vezi Copilot Integrado</span>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="<?= url('assets/images/vzl_os_finops.png'); ?>" class="d-block w-100" style="height: 380px; object-fit: cover;" alt="FinOps Module">
                            <div class="position-absolute bottom-0 start-0 w-100 px-4 py-3 glass-morphism">
                                <span class="x-small text-white fw-bold uppercase tracking-widest">💰 FinOps & Facturación</span>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="<?= url('assets/images/vzl_os_realtime.png'); ?>" class="d-block w-100" style="height: 380px; object-fit: cover;" alt="RealTime Module">
                            <div class="position-absolute bottom-0 start-0 w-100 px-4 py-3 glass-morphism">
                                <span class="x-small text-white fw-bold uppercase tracking-widest">⚡ Comunicación en Tiempo Real</span>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#app-crm-expanded .carousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#app-crm-expanded .carousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>

            <!-- Right: 6 Modules Grid + CTAs -->
            <div class="col-lg-6">
                <div class="row g-3 mb-4">
                    <!-- Module 1 -->
                    <div class="col-6">
                        <div class="p-3 rounded-4 border border-white-10 bg-white-02 hover-lift transition-all h-100">
                            <span class="material-symbols-outlined text-primary fs-3 mb-2 d-block">confirmation_number</span>
                            <h5 class="text-white h6 fw-bold mb-1">CRM & Tickets</h5>
                            <p class="text-white-50 xx-small mb-0">Ciclo comercial completo desde la solicitud hasta la entrega.</p>
                        </div>
                    </div>
                    <!-- Module 2 -->
                    <div class="col-6">
                        <div class="p-3 rounded-4 border border-white-10 bg-white-02 hover-lift transition-all h-100">
                            <span class="material-symbols-outlined text-warning fs-3 mb-2 d-block">receipt_long</span>
                            <h5 class="text-white h6 fw-bold mb-1">FinOps & Facturación</h5>
                            <p class="text-white-50 xx-small mb-0">Presupuestos, facturas y Event Sourcing contable.</p>
                        </div>
                    </div>
                    <!-- Module 3 -->
                    <div class="col-6">
                        <div class="p-3 rounded-4 border border-white-10 bg-white-02 hover-lift transition-all h-100">
                            <span class="material-symbols-outlined text-success fs-3 mb-2 d-block">folder_shared</span>
                            <h5 class="text-white h6 fw-bold mb-1">Workspace Colaborativo</h5>
                            <p class="text-white-50 xx-small mb-0">Entregables, Kanban, Timelines y aprobaciones.</p>
                        </div>
                    </div>
                    <!-- Module 4 -->
                    <div class="col-6">
                        <div class="p-3 rounded-4 border border-white-10 bg-white-02 hover-lift transition-all h-100">
                            <span class="material-symbols-outlined text-info fs-3 mb-2 d-block">bar_chart</span>
                            <h5 class="text-white h6 fw-bold mb-1">Dashboards BI</h5>
                            <p class="text-white-50 xx-small mb-0">KPIs, MRR, ARR y Churn Analysis en tiempo real.</p>
                        </div>
                    </div>
                    <!-- Module 5 -->
                    <div class="col-6">
                        <div class="p-3 rounded-4 border border-white-10 bg-white-02 hover-lift transition-all h-100">
                            <span class="material-symbols-outlined text-magenta fs-3 mb-2 d-block" style="color: var(--vzl-magenta);">smart_toy</span>
                            <h5 class="text-white h6 fw-bold mb-1">Vezi Copilot</h5>
                            <p class="text-white-50 xx-small mb-0">Resúmenes, action items y asistente de respuestas.</p>
                        </div>
                    </div>
                    <!-- Module 6 -->
                    <div class="col-6">
                        <div class="p-3 rounded-4 border border-white-10 bg-white-02 hover-lift transition-all h-100">
                            <span class="material-symbols-outlined text-accent fs-3 mb-2 d-block">manage_accounts</span>
                            <h5 class="text-white h6 fw-bold mb-1">Portal de Clientes</h5>
                            <p class="text-white-50 xx-small mb-0">Acceso self-service con visibilidad total del proyecto.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    <a href="#contacto" class="btn vzl-btn-glow-magenta px-5 py-3 fw-bold uppercase tracking-widest w-100">
                        <span class="material-symbols-outlined align-middle me-2">desktop_windows</span>Solicitar Demo App-CRM
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Flow: ¿Listo para transformar tu visión? -->
<section id="contacto" class="bg-midnight border-top border-white-5 py-5">
    <div class="container py-5">
        <div class="glass-morphism rounded-5 p-5 text-center border-white-10 shadow-2xl">
            <div class="vzl-section-header">
                <h6 class="vzl-section-subtitle">Impulsa tu éxito</h6>
                <h2 class="vzl-section-title">Tu próximo ciclo de optimización <span class="vzl-text-gradient-vibrant">empieza aquí.</span></h2>
            </div>
            <p class="lead text-white-50 mx-auto mb-5" style="max-width: 700px;">No enviamos "presupuestos" genéricos. Diseñamos un diagnóstico inicial, una propuesta de arquitectura y estimación de KPIs para tu empresa.</p>

            <div id="dynamic-ticket-flow">
                <div class="mb-5">
                    <h5 class="text-primary x-small fw-bold mb-4 text-uppercase tracking-widest">Paso 1: Selecciona un Pilar</h5>
                    <div id="pillar-selection" class="row g-3 justify-content-center">
                        <?php foreach ($categories as $category): ?>
                            <div class="col-6 col-md-3">
                                <div class="pillar-card bg-deep-black glass-morphism rounded-5 p-4 transition-all cursor-pointer h-100 hover-lift border border-white-5" data-id="<?= $category['id']; ?>" data-name="<?= $category['name']; ?>" onclick="GuidedFlow.selectPillar(this)">
                                    <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-white-5 text-primary shadow-gold" style="width: 50px; height: 50px;">
                                        <span class="material-symbols-outlined"><?= $category['icon']; ?></span>
                                    </div>
                                    <h6 class="text-white fw-bold mb-0 x-small uppercase"><?= $category['name']; ?></h6>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div id="flow-summary" class="mb-5 d-none">
                    <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                        <span id="summary-pillar" class="badge rounded-pill bg-white-5 text-primary px-4 py-2 border border-white-10"></span>
                        <span id="summary-arrow-1" class="material-symbols-outlined text-primary fs-5 d-none">double_arrow</span>
                        <span id="summary-service" class="badge rounded-pill bg-white-10 text-white px-4 py-2 border border-white-10 d-none"></span>
                    </div>
                </div>

                <div id="service-step" class="mb-5 d-none">
                    <h5 class="text-primary x-small fw-bold mb-4 text-uppercase tracking-widest">Paso 2: ¿Qué servicio necesitas?</h5>
                    <div id="service-selection" class="row g-3 justify-content-center"></div>
                </div>

                <div id="plan-step" class="mb-5 d-none">
                    <h5 class="text-primary x-small fw-bold mb-4 text-uppercase tracking-widest">Paso 3: Elige un Plan</h5>
                    <div id="plan-selection" class="row g-3 justify-content-center"></div>
                </div>

                <div id="form-step" class="text-start d-none">
                    <div class="glass-morphism p-4 rounded-5 border-white-10 shadow-lg mx-auto" style="max-width: 800px;">
                        <h4 class="text-white fw-black mb-4">Completa tu solicitud</h4>
                        <form id="dynamic-ticket-form" action="<?= url('ticket/submit'); ?>" method="POST">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="service_plan_id" id="selected-plan-id">
                            <div class="row g-4">
                                <div class="col-md-6"><input type="text" name="name" class="form-control bg-white-5 border-white-10 text-white" placeholder="Nombre completo" required></div>
                                <div class="col-md-6"><input type="email" name="email" class="form-control bg-white-5 border-white-10 text-white" placeholder="Correo corporativo" required></div>
                                <div class="col-12"><input type="text" name="subject" id="form-subject" class="form-control bg-white-5 border-white-10 text-white" placeholder="Asunto" required></div>
                                <div class="col-12"><textarea name="description" class="form-control bg-white-5 border-white-10 text-white" rows="4" placeholder="Cuéntanos sobre tu proyecto..." required></textarea></div>
                                <div class="col-12"><button type="submit" class="btn btn-primary w-100 py-3 text-dark uppercase fw-bold tracking-widest">Enviar Solicitud</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section Refined -->
<section id="faq" class="py-5 bg-midnight overflow-hidden cta-parallax-bg" style="padding: 7rem 0 !important;">
    <div class="container py-5">
        <div class="max-w-800 mx-auto glass-morphism p-5 rounded-5 border-white-10 shadow-2xl">
            <div class="vzl-section-header">
                <h6 class="vzl-section-subtitle">Soporte y Consultoría</h6>
                <h2 class="vzl-section-title">Preguntas <span class="vzl-text-gradient-vibrant">Frecuentes</span></h2>
                <p class="text-white-50 mt-3">Todo lo que necesitas saber sobre nuestro proceso de trabajo.</p>
            </div>
            
            <div class="vzl-faq-accordion">
                <!-- FAQ 01 — open by default -->
                <div class="vzl-faq-item vzl-faq-active mb-3 border border-white-05 rounded-4 bg-white-02 overflow-hidden">
                    <div class="vzl-faq-question cursor-pointer d-flex justify-content-between align-items-center p-4" onclick="FAQ.toggle(this)">
                        <h4 class="h6 text-white mb-0 fw-bold">¿Cómo inicio un ciclo de trabajo con <?= mb_strtoupper(\Core\Config::get('business.company_name')) ?>?</h4>
                        <span class="material-symbols-outlined text-primary fs-4 transition-all" style="transform: rotate(180deg);">expand_more</span>
                    </div>
                    <div class="vzl-faq-answer px-4 pb-4">
                        <p class="text-white-50 small mb-0">Todo inicia con un diagnóstico técnico. En lugar de enviar un brief genérico, selecciona en nuestra sección de contacto el pilar operativo que necesitas optimizar. Nuestro equipo analizará tu caso y agendará una sesión para plantear una arquitectura técnica, definir KPIs y establecer el roadmap de ejecución.</p>
                    </div>
                </div>

                <!-- FAQ 02 -->
                <div class="vzl-faq-item mb-3 border border-white-05 rounded-4 bg-white-02 overflow-hidden">
                    <div class="vzl-faq-question cursor-pointer d-flex justify-content-between align-items-center p-4" onclick="FAQ.toggle(this)">
                        <h4 class="h6 text-white mb-0 fw-bold">¿Qué respaldo técnico tienen las arquitecturas que desarrollan?</h4>
                        <span class="material-symbols-outlined text-primary fs-4 transition-all">expand_more</span>
                    </div>
                    <div class="vzl-faq-answer px-4 pb-4 d-none">
                        <p class="text-white-50 small mb-0">Enfoque en escalabilidad y estabilidad total. Operamos bajo estrictos estándares de ingeniería de software: código documentado, pruebas automatizadas, monitoreo de rendimiento y SLAs (Acuerdos de Nivel de Servicio) definidos. Además, todas las infraestructuras incluyen un ciclo de soporte evolutivo post-despliegue para garantizar adopción y eficiencia.</p>
                    </div>
                </div>

                <!-- FAQ 03 -->
                <div class="vzl-faq-item mb-3 border border-white-05 rounded-4 bg-white-02 overflow-hidden">
                    <div class="vzl-faq-question cursor-pointer d-flex justify-content-between align-items-center p-4" onclick="FAQ.toggle(this)">
                        <h4 class="h6 text-white mb-0 fw-bold">¿Cuáles son los tiempos de despliegue y Go-to-Market?</h4>
                        <span class="material-symbols-outlined text-primary fs-4 transition-all">expand_more</span>
                    </div>
                    <div class="vzl-faq-answer px-4 pb-4 d-none">
                        <p class="text-white-50 small mb-0">Diseñamos para la velocidad sin comprometer la infraestructura. Un Minimum Viable Product (MVP) operativo o una integración de datos puede desplegarse en 3 a 5 semanas. Para plataformas complejas, App-CRMs a medida o arquitecturas FinOps, operamos mediante sprints iterativos que entregan valor utilizable mes a mes.</p>
                    </div>
                </div>

                <!-- FAQ 05 — NEW -->
                <div class="vzl-faq-item mb-3 border border-white-05 rounded-4 bg-white-02 overflow-hidden">
                    <div class="vzl-faq-question cursor-pointer d-flex justify-content-between align-items-center p-4" onclick="FAQ.toggle(this)">
                        <h4 class="h6 text-white mb-0 fw-bold">¿Mis datos están seguros con <?= \Core\Config::get('business.company_name') ?>?</h4>
                        <span class="material-symbols-outlined text-primary fs-4 transition-all">expand_more</span>
                    </div>
                    <div class="vzl-faq-answer px-4 pb-4 d-none">
                        <p class="text-white-50 small mb-0">La seguridad es nuestra prioridad arquitectónica, no un add-on. Todas nuestras plataformas implementan <strong class="text-white">criptografía Argon2id</strong> para contraseñas, <strong class="text-white">Autenticación de Doble Factor (2FA)</strong> para accesos críticos, <strong class="text-white">registros de auditoría inmutables con firma SHA256</strong> y protección contra inyección SQL y ataques CSRF. Tu información está protegida bajo estándares de seguridad enterprise.</p>
                    </div>
                </div>

                <!-- FAQ 06 — NEW -->
                <div class="vzl-faq-item mb-3 border border-white-05 rounded-4 bg-white-02 overflow-hidden">
                    <div class="vzl-faq-question cursor-pointer d-flex justify-content-between align-items-center p-4" onclick="FAQ.toggle(this)">
                        <h4 class="h6 text-white mb-0 fw-bold">¿Implementan el App-CRM en mi empresa?</h4>
                        <span class="material-symbols-outlined text-primary fs-4 transition-all">expand_more</span>
                    </div>
                    <div class="vzl-faq-answer px-4 pb-4 d-none">
                        <p class="text-white-50 small mb-0">Sí. La <strong class="text-white">Implementación CRM</strong> es uno de nuestros servicios dentro del pilar "Aplicaciones y Web Apps". Podemos desplegar y adaptar nuestro App-CRM (o integrarte con Bitrix24, Dynamics o Salesforce según tu contexto) con todas las configuraciones, integraciones y capacitación del equipo incluidas. Empieza solicitando un diagnóstico gratuito desde la sección de contacto.</p>
                    </div>
                </div>

                <!-- FAQ 07 — NEW -->
                <div class="vzl-faq-item border border-white-05 rounded-4 bg-white-02 overflow-hidden">
                    <div class="vzl-faq-question cursor-pointer d-flex justify-content-between align-items-center p-4" onclick="FAQ.toggle(this)">
                        <h4 class="h6 text-white mb-0 fw-bold">¿Trabajamos con equipos técnicos o no técnicos?</h4>
                        <span class="material-symbols-outlined text-primary fs-4 transition-all">expand_more</span>
                    </div>
                    <div class="vzl-faq-answer px-4 pb-4 d-none">
                        <p class="text-white-50 small mb-0">Ambos. Nuestro <strong class="text-white">Portal de Clientes</strong> está diseñado para que cualquier persona de tu empresa pueda hacer seguimiento de proyectos, aprobar presupuestos y descargar entregables sin necesitar conocimientos técnicos. Al mismo tiempo, el panel de administración y los módulos de BI y ETL están diseñados para equipos de ingeniería y análisis de datos. Sin barreras de entrada, sin techo tecnológico.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partners Section -->
<?php
$partnersDir = 'assets/images/socios/';
$partners = [];
if (is_dir(public_path($partnersDir))) {
    $files = scandir(public_path($partnersDir));
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && (strpos($file, '.png') !== false || strpos($file, '.jpg') !== false || strpos($file, '.svg') !== false)) {
            $partners[] = url($partnersDir . $file);
        }
    }
}
?>
<?php if (!empty($partners)): ?>
<section class="partners-section bg-deep-black py-5 border-top border-white-5">
    <div class="container text-center mb-5">
        <h6 class="text-white-50 x-small tracking-widest uppercase fw-bold">Ellos también confían en <?= \Core\Config::get('business.company_name') ?></h6>
    </div>
    <div class="container pb-2">
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-5">
            <?php foreach ($partners as $logo): ?>
                <img src="<?= $logo ?>" alt="Partner" style="height: 90px; width: auto; filter: grayscale(1) opacity(0.5); transition: all 0.4s ease;" class="hover-opacity-1 hover-lift">
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Blog Preview (Now the last section) -->
<section id="blog" class="bg-deep-black border-top border-white-5 py-5">
    <div class="container py-5">
        <div class="vzl-section-header">
            <h6 class="vzl-section-subtitle">Insights & Conocimiento</h6>
            <h2 class="vzl-section-title">Tech <span class="vzl-text-gradient-vibrant">Blog</span></h2>
        </div>
        <div class="row g-4" id="blog-grid">
            <?php foreach ($latestPosts as $post): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-white-10 bg-midnight overflow-hidden rounded-5 hover-lift transition-all">
                        <img src="<?= url($post['featured_image']); ?>" class="card-img-top" style="height: 200px; object-fit: cover; opacity: 0.8;">
                        <div class="card-body p-4">
                            <h5 class="text-white fw-bold h6 mb-3"><?= $post['title']; ?></h5>
                            <p class="text-white-50 x-small mb-4"><?= $post['excerpt']; ?></p>
                            <a href="<?= url('blog/post/' . $post['slug']); ?>" class="text-primary text-decoration-none small fw-bold">Leer Más →</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= url('blog'); ?>" class="btn btn-outline-white px-5 py-3 small fw-bold uppercase">Ver Más</a>
        </div>
    </div>
</section>



<script>
    // Animated Counters
    const counters = document.querySelectorAll('.vzl-counter-display');
    const animateCounters = () => {
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText.replace(/k/g, '');
                // Calculate increment based on target size for smooth 2-second animation
                const frames = 60; 
                const inc = target / frames;

                if (count < target) {
                    const newValue = Math.ceil(count + inc);
                    if(target >= 1000 && newValue >= 1000) {
                        counter.innerText = (newValue / 1000).toFixed(1).replace('.0','') + 'k';
                    } else {
                        counter.innerText = newValue;
                    }
                    setTimeout(updateCount, 30);
                } else {
                    if(target >= 1000) {
                        counter.innerText = Math.floor(target / 1000) + 'k';
                    } else {
                        counter.innerText = target;
                    }
                }
            };
            updateCount();
        });
    }

    const observer = new IntersectionObserver((entries) => {
        if(entries[0].isIntersecting) {
            animateCounters();
            observer.disconnect();
        }
    }, { threshold: 0.5 });
    
    if (counters.length > 0) {
        const proofSection = document.querySelector('.vzl-social-proof');
        if (proofSection) observer.observe(proofSection);
    }

    // FAQ Modern Toggle
    window.FAQ = {
        toggle: function(element) {
            const item = element.parentElement;
            const answer = item.querySelector('.vzl-faq-answer');
            const icon = item.querySelector('.material-symbols-outlined');
            
            const isOpen = !answer.classList.contains('d-none');
            
            // Close others
            document.querySelectorAll('.vzl-faq-answer').forEach(a => a.classList.add('d-none'));
            document.querySelectorAll('.vzl-faq-question .material-symbols-outlined').forEach(i => i.style.transform = 'rotate(0deg)');
            
            if (!isOpen) {
                answer.classList.remove('d-none');
                icon.style.transform = 'rotate(180deg)';
                item.classList.add('vzl-faq-active');
            } else {
                item.classList.remove('vzl-faq-active');
            }
        }
    };

    // Parallax Effect
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY;
        const heroVideo = document.querySelector('.hero-video');
        if (heroVideo) {
            heroVideo.style.transform = `translateY(${scrolled * 0.4}px) scale(1.1)`;
        }
    });

</script>

<style>
    /* Parallax global rule for home */
    .cta-parallax-bg {
        background: linear-gradient(rgba(10, 10, 14, 0.85), rgba(10, 10, 14, 0.85)), url('<?= url("assets/images/hero_background.png") ?>') !important;
        background-size: cover !important;
        background-position: center !important;
        background-attachment: fixed !important;
    }
</style>
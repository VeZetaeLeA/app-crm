<section class="min-vh-60 d-flex align-items-center position-relative overflow-hidden pt-5 pb-5">
    <!-- Parallax Background -->
    <div class="position-absolute top-0 start-0 w-100 h-100 zoom-parallax"
        style="background: linear-gradient(rgba(10, 11, 14, 0.7), rgba(10, 11, 14, 0.9)), url('<?php echo $service['category_image'] ? url($service['category_image']) : url('assets/images/hero_fallback.jpg'); ?>') center/cover no-repeat; transform: scale(1.1); z-index: 0;">
    </div>

    <div class="container pt-5 position-relative" style="z-index: 1;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>"
                        class="text-primary text-decoration-none small fw-bold uppercase px-0 shadow-sm">Inicio</a></li>
                <li class="breadcrumb-item"><a
                        href="<?php echo url('service/category/' . $service['category_slug']); ?>"
                        class="text-primary text-decoration-none small fw-bold uppercase px-0 shadow-sm">
                        <?php echo $service['category_name']; ?>
                    </a></li>
                <li class="breadcrumb-item active text-white-50 small fw-bold uppercase" aria-current="page">
                    <?php echo $service['name']; ?>
                </li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div
                        class="rounded-circle bg-midnight text-accent p-3 backdrop-blur border border-white-10 flex-shrink-0 icon-container">
                        <span class="material-symbols-outlined fs-1">
                            <?php echo $service['icon'] ?? 'bolt'; ?>
                        </span>
                    </div>
                    <h1 class="h2 display-5 fw-black text-white mb-0 tracking-tighter text-gradient text-nowrap">
                        <?php echo $service['name']; ?>
                    </h1>
                </div>
                <p class="text-white-50 overflow-hidden" style="max-width: 750px; line-height: 1.6;">
                    <span
                        class="d-block bg-primary bg-opacity-10 p-3 p-md-4 rounded-4 border-start border-primary border-4 backdrop-blur shadow-2xl small">
                        <?php echo $service['full_description']; ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section-fluid-y bg-deep-black">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold text-uppercase tracking-widest mb-3">Planes & Precios</h6>
            <h2 class="display-5 fw-bold text-white mb-3">Elige la solución a tu medida</h2>
            <div class="bg-primary mx-auto rounded-pill" style="width: 60px; height: 4px;"></div>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($plans as $plan): ?>
                <div class="col-md-4">
                    <div
                        class="card bg-midnight border-white-10 h-100 p-4 d-flex flex-column hover-lift transition-all <?php echo $plan['is_featured'] ? 'border-primary shadow-gold transform-scale-105 z-1' : ''; ?>">
                        <?php if ($plan['is_featured']): ?>
                            <div class="position-absolute top-0 start-50 translate-middle">
                                <span class="badge bg-primary text-deep-black fw-bold px-3 py-2 uppercase tracking-tighter">Más
                                    Popular</span>
                            </div>
                        <?php endif; ?>

                        <div class="text-center flex-grow-1">
                            <h4 class="text-white h6 fw-bold uppercase mb-2 text-gradient">
                                <?php echo $plan['name']; ?>
                                <?php if ($plan['is_featured']): ?>
                                    <span class="ms-1" title="Más Popular">⭐</span>
                                <?php endif; ?>
                            </h4>
                            <div class="d-flex align-items-baseline justify-content-center gap-1 mb-4"
                                style="min-height: 4rem;">
                                <?php if ($plan['price'] > 0): ?>
                                    <span class="text-white-50 x-small">$</span>
                                    <span class="display-6 fw-black text-white">
                                        <?php echo number_format($plan['price'], 0); ?>
                                    </span>
                                    <span class="text-white-50 x-small">u$d</span>
                                <?php else: ?>
                                    <p class="text-white small mb-0 italic text-center px-3" style="line-height: 1.4;">
                                        * El precio varía según complejidad del proyecto
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4 d-flex flex-column" style="min-height: 180px;">
                                <ul class="list-unstyled text-white-50 small mb-0">
                                    <?php
                                    $features = json_decode($plan['features'], true);
                                    if ($features):
                                        foreach ($features as $feature):
                                            ?>
                                            <li class="mb-2 d-flex align-items-start gap-2">
                                                <span class="material-symbols-outlined text-primary fs-6">check_circle</span>
                                                <span class="x-small">
                                                    <?php echo $feature; ?>
                                                </span>
                                            </li>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-auto pt-3">
                            <button
                                onclick="orderPlan(<?php echo $plan['id']; ?>, '<?php echo $service['name'] . ' - ' . $plan['name']; ?>')"
                                class="btn <?php echo $plan['is_featured'] ? 'vzl-btn-glow-magenta fw-bold uppercase tracking-widest' : 'btn-outline-white small fw-bold uppercase'; ?> w-100 py-3 rounded-pill">
                                <?php echo ($plan['price'] == 0) ? 'Cotizar' : 'Seleccionar Plan'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section with Dynamic Flow -->
<section id="contacto" class="pt-5 position-relative overflow-hidden cta-parallax-bg border-top border-white-5 d-none">
    <div class="container py-5 position-relative z-1">
        <div class="glass-morphism rounded-5 p-5 p-lg-5 text-center border-white-10 shadow-2xl">
            <h2 class="display-5 fw-black text-white mb-4">¿Listo para transformar tus datos?</h2>
            <p class="lead text-white-50 mx-auto mb-5" style="max-width: 700px;">
                Únete a las empresas líderes que ya optimizan sus procesos con <?= \Core\Config::get('business.company_name') ?>. Diseñemos juntos el futuro de
                tu infraestructura.
            </p>

            <div id="dynamic-ticket-flow">
                <!-- Level 4: Form (In detail page we skip the steps) -->
                <div id="form-step" class="text-start">
                    <div class="glass-morphism p-4 p-md-5 rounded-4 border-white-10 shadow-lg bg-white-5">
                        <h4 class="text-white fw-black mb-4 h3">Completa tu solicitud para <span class="text-gold"
                                id="selected-plan-text">...</span></h4>
                        <form id="dynamic-ticket-form" action="<?php echo url('ticket/submit'); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="service_plan_id" id="selected-plan-id">

                            <?php /* ── ANTI-BOT FIELDS (Fricción Cero) ─────────────────────────── */ ?>
                            <div style="position: absolute; left: -9999px; top: -9999px;" aria-hidden="true">
                                <label for="_contact_website_url_check">Si eres humano, deja este campo vacío</label>
                                <input type="text" name="_contact_website_url_check" id="_contact_website_url_check_detail" tabindex="-1" autocomplete="new-password">
                            </div>
                            <input type="hidden" name="_vzl_load_time" id="_vzl_load_time_detail" value="">

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="text-white-50 x-small uppercase tracking-widest fw-bold mb-2">Nombre
                                        Completo</label>
                                    <input type="text" name="name"
                                        class="form-control bg-midnight border-white-10 text-white p-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-white-50 x-small uppercase tracking-widest fw-bold mb-2">Correo
                                        Electrónico</label>
                                    <input type="email" name="email"
                                        class="form-control bg-midnight border-white-10 text-white p-3" required>
                                </div>
                                <div class="col-12">
                                    <label
                                        class="text-white-50 x-small uppercase tracking-widest fw-bold mb-2">Asunto</label>
                                    <input type="text" name="subject" id="form-subject"
                                        class="form-control bg-midnight border-white-10 text-white p-3" readonly
                                        required>
                                </div>
                                <div class="col-12">
                                    <label
                                        class="text-white-50 x-small uppercase tracking-widest fw-bold mb-2">Descripción
                                        del Proyecto</label>
                                    <textarea name="description"
                                        class="form-control bg-midnight border-white-10 text-white p-3" rows="4"
                                        placeholder="Cuéntanos un poco más sobre tus necesidades..."
                                        required></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" id="detail-ticket-submit-btn"
                                        class="btn vzl-btn-glow-magenta w-100 py-3 fw-bold uppercase tracking-widest">
                                        Enviar Solicitud <span
                                            class="material-symbols-outlined ms-2 align-middle">send</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function orderPlan(planId, planName) {
        const contactSection = document.getElementById('contacto');
        contactSection.classList.remove('d-none');

        document.getElementById('selected-plan-id').value = planId;
        document.getElementById('form-subject').value = `Solicitud: ${planName}`;
        document.getElementById('selected-plan-text').innerText = planName;

        // Initialize load-time when the form becomes visible
        const loadTimeField = document.getElementById('_vzl_load_time_detail');
        if (loadTimeField && !loadTimeField.value) {
            loadTimeField.value = Math.floor(Date.now() / 1000);
        }

        contactSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Pre-select plan from URL (E11-OS)
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const preselect = urlParams.get('preselect');
        
        if (preselect) {
            // Mapping from PHP
            const plans = <?php echo json_encode(array_map(function($p) use ($service) {
                return ['id' => $p['id'], 'name' => $service['name'] . ' - ' . $p['name']];
            }, $plans)); ?>;
            
            const targetPlan = plans.find(p => p.name.includes(preselect));
            if (targetPlan) {
                setTimeout(() => {
                    orderPlan(targetPlan.id, targetPlan.name);
                }, 500);
            }
        }
    });
</script>

<?php $recaptchaKey = \Core\Config::get('security.recaptcha_site_key'); ?>
<?php if (!empty($recaptchaKey)): ?>
<!-- reCAPTCHA v3 — Service Detail Ticket Form -->
<script>
    let recaptchaLoadedDetail = false;
    let recaptchaKeyDetail = '<?= $recaptchaKey ?>';

    function loadRecaptchaDetail() {
        if (recaptchaLoadedDetail) return;
        recaptchaLoadedDetail = true;
        const script = document.createElement('script');
        script.src = "https://www.google.com/recaptcha/api.js?render=" + recaptchaKeyDetail;
        document.head.appendChild(script);
        document.body.classList.add('show-recaptcha');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const detailForm = document.getElementById('dynamic-ticket-form');
        if (detailForm) {
            // Cargar reCAPTCHA solo cuando el usuario comienza a llenar el formulario (focus)
            const inputs = detailForm.querySelectorAll('input:not([type="hidden"]), textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', loadRecaptchaDetail, { once: true });
            });

            detailForm.addEventListener('submit', function (e) {
                // Clear honeypot right before submit
                const honeypot = document.getElementById('_contact_website_url_check_detail');
                if (honeypot) honeypot.value = '';

                if (detailForm.querySelector('input[name="g-recaptcha-response"]')) {
                    return; // Permitir el envío
                }

                e.preventDefault();
                const submitBtn = document.getElementById('detail-ticket-submit-btn');
                const originalText = submitBtn ? submitBtn.innerHTML : '';

                if (typeof grecaptcha === 'undefined') {
                    console.warn('[reCAPTCHA] Script not loaded yet. Waiting...');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = 'Verificando red...';
                    }
                    setTimeout(() => {
                        if (typeof grecaptcha !== 'undefined') detailForm.requestSubmit();
                        else alert('Error de seguridad: Sistema de validación no disponible.');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }, 2000);
                    return;
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Verificando... <span class="material-symbols-outlined ms-2 align-middle" style="animation:rotate 1.5s linear infinite;display:inline-block">autorenew</span>';
                }
                
                grecaptcha.ready(function () {
                    grecaptcha.execute(recaptchaKeyDetail, { action: 'ticket_request' }).then(function (token) {
                        detailForm.insertAdjacentHTML('beforeend', '<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
                        detailForm.submit();
                    }).catch(function () {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                        alert('No se pudo verificar la prueba de seguridad. Intente de nuevo.');
                    });
                });
            });
        }
    });
</script>
<style>
/* Control visual del badge de ReCaptcha */
body:not(.show-recaptcha) .grecaptcha-badge { visibility: hidden !important; opacity: 0 !important; pointer-events: none; }
</style>
<?php endif; ?>

<style>
    .transform-scale-105 {
        transform: scale(1.05);
    }

    .z-1 {
        z-index: 1;
    }

    .min-vh-60 {
        min-height: 60vh;
    }

    .breadcrumb-item+.breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.3);
    }

    .zoom-parallax {
        animation: subtleZoom 20s infinite alternate linear;
    }

    @keyframes subtleZoom {
        from {
            transform: scale(1.05) translateY(0);
        }

        to {
            transform: scale(1.15) translateY(-20px);
        }
    }

    .backdrop-blur {
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
    }

    .tracking-tighter {
        letter-spacing: -2px;
    }

    .shadow-2xl {
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .cta-parallax-bg {
        background: linear-gradient(rgba(10, 10, 10, 0.8), rgba(10, 10, 10, 0.8)), url('<?php echo url($service['category_image']); ?>');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .text-gold {
        color: var(--vzl-color-gold);
    }

    /* Action Buttons in Selection */
    .btn-muted-outline {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: rgba(255, 255, 255, 0.4) !important;
        transition: all 0.3s ease;
    }

    .card:hover .btn-muted-outline {
        background: var(--vzl-color-info) !important;
        border-color: var(--vzl-color-info) !important;
        color: white !important;
        box-shadow: 0 0 15px rgba(48, 197, 255, 0.4);
    }

    .card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card:hover {
        transform: translateY(-8px);
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--vzl-color-gold) !important;
        box-shadow: 0 15px 35px -10px rgba(212, 175, 55, 0.3) !important;
    }
</style>
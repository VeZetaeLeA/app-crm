<section class="min-vh-60 d-flex align-items-center position-relative overflow-hidden pt-5 pb-5">
    <!-- Brand Background -->
    <div class="position-absolute top-0 start-0 w-100 h-100 zoom-parallax"
        style="background: linear-gradient(rgba(10, 11, 14, 0.8), rgba(10, 11, 14, 0.9)), url('<?php echo url('assets/images/hero_background.png'); ?>') center/cover no-repeat; z-index: 0;">
    </div>

    <div class="container pt-5 text-center position-relative" style="z-index: 1;">
        <h1 class="display-5 fw-black text-white mb-4 tracking-tighter">Inicia tu <span
                class="text-gradient">Transformación</span></h1>
        <p class="lead text-white-50 mx-auto" style="max-width: 700px;">
            Sigue los pasos para diseñar la solución ideal que llevará tu negocio al siguiente nivel.
        </p>
        
        <!-- Selection Summary -->
        <div id="flow-summary" class="mt-4 mb-2 d-none">
            <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                <span id="summary-pillar" class="badge rounded-pill bg-white-5 text-primary px-4 py-2 border border-white-10"></span>
                <span id="summary-arrow-1" class="material-symbols-outlined text-primary fs-5 d-none">double_arrow</span>
                <span id="summary-service" class="badge rounded-pill bg-white-10 text-white px-4 py-2 border border-white-10 d-none"></span>
            </div>
        </div>
    </div>
</section>

<style>
    .min-vh-60 { min-height: 60vh; }
    .zoom-parallax { animation: subtleZoom 20s infinite alternate linear; }
    @keyframes subtleZoom { from { transform: scale(1.05); } to { transform: scale(1.15); } }
    .tracking-tighter { letter-spacing: -2px; }
    .cursor-pointer { cursor: pointer; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .transition-all { transition: all 0.3s ease; }
</style>

<section class="py-5 brand-bg">
    <div class="container py-5">
        
        <!-- Step 1: Category Selection -->
        <div id="pillar-step" class="mb-5">
            <h5 class="text-white-50 x-small fw-bold mb-4 uppercase tracking-widest text-center">Paso 1: ¿En qué área nos enfocamos?</h5>
            <div class="row g-4 justify-content-center">
                <?php foreach ($categories as $category): ?>
                    <div class="col-6 col-md-3">
                        <div class="glass-morphism rounded-5 p-4 text-center transition-all cursor-pointer h-100 hover-lift border border-white-5"
                            data-id="<?= $category['id']; ?>"
                            data-name="<?= $category['name']; ?>"
                            onclick="GuidedFlow.selectPillar(this)">
                            <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-white-5 text-primary shadow-gold" style="width: 50px; height: 50px;">
                                <span class="material-symbols-outlined"><?= $category['icon']; ?></span>
                            </div>
                            <h6 class="text-white fw-bold mb-0 x-small uppercase"><?= $category['name']; ?></h6>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Step 2: Service Selection -->
        <div id="service-step" class="mb-5 d-none">
            <h5 class="text-white-50 x-small fw-bold mb-4 uppercase tracking-widest text-center">Paso 2: Selecciona el Servicio</h5>
            <div id="service-selection" class="row g-4 justify-content-center">
                <!-- Dynamically populated -->
            </div>
        </div>

        <!-- Step 3: Plan Selection -->
        <div id="plan-step" class="mb-5 d-none">
            <h5 class="text-white-50 x-small fw-bold mb-4 uppercase tracking-widest text-center">Paso 3: Elige la modalidad</h5>
            <div id="plan-selection" class="row g-4 justify-content-center">
                <!-- Dynamically populated -->
            </div>
        </div>

        <!-- Step 4: Final Form -->
        <div id="form-step" class="row justify-content-center d-none">
            <div class="col-lg-8">
                <div class="glass-morphism-premium p-4 p-lg-5 rounded-5 shadow-2xl">
                    <h4 class="text-white fw-black mb-4 uppercase tracking-widest text-center small">Cuentanos sobre tu Requerimiento</h4>
                    <form action="<?php echo url('ticket/submit'); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="service_plan_id" id="selected-plan-id">
                        
                        <!-- HONEYPOT & ANTI-SPAM FIELDS (Fricción Cero) -->
                        <div style="position: absolute; left: -9999px; top: -9999px;" aria-hidden="true">
                            <label for="_vzl_security_trap">Si eres humano, deja este campo vacío</label>
                            <input type="text" name="_vzl_security_trap" id="_vzl_security_trap" tabindex="-1" autocomplete="off">
                        </div>
                        <input type="hidden" name="_vzl_load_time" value="<?php echo time(); ?>">
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="text-white-50 x-small mb-2 uppercase tracking-widest fw-bold">Nombre Completo</label>
                                <input type="text" name="name" class="form-control p-2 small" placeholder="Ej: Juan Pérez" required
                                    value="<?php echo \Core\Auth::check() ? \Core\Auth::user()['name'] : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="text-white-50 x-small mb-2 uppercase tracking-widest fw-bold">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control p-2 small" placeholder="tu@email.com" required
                                    value="<?php echo \Core\Auth::check() ? \Core\Auth::user()['email'] : ''; ?>">
                            </div>
                            <div class="col-12">
                                <label class="text-white-50 x-small mb-2 uppercase tracking-widest fw-bold">Asunto</label>
                                <input type="text" name="subject" id="form-subject" class="form-control p-2 small" placeholder="Ej: Consulta sobre Pipeline de Datos" required>
                            </div>
                            <div class="col-12">
                                <label class="text-white-50 x-small mb-2 uppercase tracking-widest fw-bold">Descripción detallada</label>
                                <textarea name="description" class="form-control p-2 small" rows="5" placeholder="Cuéntanos los detalles, objetivos y desafíos..." required></textarea>
                            </div>

                            <div class="col-12 mt-4 text-center">
                                <button type="submit" class="btn btn-primary px-5 py-3 shadow-gold fw-bold uppercase">
                                    Enviar Solicitud <span class="material-symbols-outlined ms-2 fs-6">send</span>
                                </button>
                                <p class="text-white-50 x-small mt-4 mb-0">
                                    Un representante técnico revisará tu solicitud y se contactará en menos de 24 horas.
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $recaptchaKey = \Core\Config::get('security.recaptcha_site_key'); ?>
<?php if (!empty($recaptchaKey)): ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?= $recaptchaKey ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action$="ticket/submit"]');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!form.querySelector('input[name="g-recaptcha-response"]')) {
                    e.preventDefault();
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Verificando seguridad... <span class="material-symbols-outlined ms-2 fs-6 pb-1 rotating">autorenew</span>';
                    
                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?= $recaptchaKey ?>', {action: 'ticket_request'}).then(function(token) {
                            form.insertAdjacentHTML('beforeend', '<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
                            form.submit();
                        }).catch(function() {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                            alert('No se pudo verificar la prueba de seguridad. Intente de nuevo.');
                        });
                    });
                }
            });
        }
    });
</script>
<style>
.rotating { animation: rotate 1.5s linear infinite; display: inline-block; vertical-align: middle; }
@keyframes rotate { 100% { transform: rotate(360deg); } }
</style>
<?php endif; ?>
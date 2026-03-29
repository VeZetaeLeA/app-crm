<section class="min-vh-100 d-flex align-items-center position-relative overflow-hidden pt-5 pb-5">
    <!-- Brand Background -->
    <div class="position-absolute top-0 start-0 w-100 h-100"
        style="background: linear-gradient(rgba(10, 11, 14, 0.8), rgba(10, 11, 14, 0.9)), url('<?php echo url('assets/images/hero_background.png'); ?>') center/cover no-repeat; z-index: 0;">
    </div>

    <div class="container pt-5 position-relative" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <!-- Success Icon -->
                <div
                    class="mb-4 d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success p-4 border border-success border-opacity-20 shadow-lg animate-pulse">
                    <span class="material-symbols-outlined display-3">check_circle</span>
                </div>

                <h1 class="display-4 fw-black text-white mb-3">Solicitud <span class="text-gradient">Recibida</span>
                </h1>
                <p class="lead text-white-50 mb-5 mx-auto" style="max-width: 600px;">
                    Gracias por confiar en <?php echo \Core\Config::get('business.company_name'); ?>. Tu requerimiento
                    ha sido ingresado en nuestro sistema. <strong>Ya hemos iniciado tu sesión</strong> para que puedas
                    monitorear el progreso desde tu panel. <br>
                    <strong class="text-white mt-2 d-inline-block">Por motivos de seguridad, es indispensable que
                        configures tu contraseña.</strong>
                </p>

                <!-- Roadmap / Proceso -->
                <div class="glass-morphism p-5 rounded-5 border-white-10 text-start mb-5 shadow-2xl">
                    <h5 class="text-primary small fw-bold mb-4 text-uppercase tracking-widest text-center">Próximos
                        Pasos</h5>

                    <div class="row g-4 pt-2">
                        <div class="col-md-6 col-lg-3 text-center roadmap-step">
                            <div class="rounded-circle bg-white-5 mx-auto mb-3 d-flex align-items-center justify-content-center text-accent"
                                style="width: 50px; height: 50px;">
                                <span class="material-symbols-outlined">assignment_turned_in</span>
                            </div>
                            <h6 class="text-white small fw-bold mb-2">Análisis</h6>
                            <p class="text-white-50 x-small mb-0">Revisamos tu requerimiento detalladamente.</p>
                        </div>
                        <div class="col-md-6 col-lg-3 text-center roadmap-step">
                            <div class="rounded-circle bg-white-5 mx-auto mb-3 d-flex align-items-center justify-content-center text-accent"
                                style="width: 50px; height: 50px;">
                                <span class="material-symbols-outlined">payments</span>
                            </div>
                            <h6 class="text-white small fw-bold mb-2">Presupuesto</h6>
                            <p class="text-white-50 x-small mb-0">Recibirás una propuesta en tu panel.</p>
                        </div>
                        <div class="col-md-6 col-lg-3 text-center roadmap-step">
                            <div class="rounded-circle bg-white-5 mx-auto mb-3 d-flex align-items-center justify-content-center text-accent"
                                style="width: 50px; height: 50px;">
                                <span class="material-symbols-outlined">lock_reset</span>
                            </div>
                            <h6 class="text-white small fw-bold mb-2">Seguridad</h6>
                            <p class="text-white-50 x-small mb-0"><span class="text-gradient fw-bold">Te recomendamos
                                    establecer tu contraseña ahora.</span></p>
                        </div>
                        <div class="col-md-6 col-lg-3 text-center roadmap-step">
                            <div class="rounded-circle bg-white-5 mx-auto mb-3 d-flex align-items-center justify-content-center text-accent"
                                style="width: 50px; height: 50px;">
                                <span class="material-symbols-outlined">dashboard</span>
                            </div>
                            <h6 class="text-white small fw-bold mb-2">Panel</h6>
                            <p class="text-white-50 x-small mb-0">Gestiona todo desde tu área privada.</p>
                        </div>
                    </div>
                </div>

                <!-- CTAs -->
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="<?php echo url('profile/settings#change-password'); ?>"
                        class="btn btn-primary px-5 py-3 fw-bold uppercase tracking-widest shadow-gold text-white">Establecer
                        Contraseña</a>
                    <a href="<?php echo url('dashboard'); ?>"
                        class="btn btn-outline-light px-5 py-3 fw-bold uppercase tracking-widest">Ir a mi
                        Panel</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .animate-pulse {
        animation: successPulse 2s infinite;
    }

    @keyframes successPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(25, 135, 84, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
        }
    }

    @media (max-width: 576px) {
        .display-4 {
            font-size: 2.5rem;
        }

        .glass-morphism.p-5 {
            padding: 2rem !important;
        }

        .roadmap-step h6 {
            font-size: 0.9rem;
        }

        .roadmap-step p {
            font-size: 0.75rem;
        }
    }
</style>
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center p-0 overflow-hidden position-relative cta-parallax-bg">
    <!-- Abstract Background Ambient Light -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-20"
        style="background: radial-gradient(circle at 50% 50%, color-mix(in srgb, var(--vzl-primary) 15%, transparent) 0%, transparent 70%);"></div>

    <div class="row g-0 w-100 align-items-center justify-content-center position-relative px-3" style="z-index: 2;">
        <div class="col-12 col-xl-10 glass-morphism rounded-5 border-white-10 shadow-2xl p-0 d-flex flex-wrap overflow-hidden" style="min-height: 500px;">
        <!-- Image Section -->
        <div class="col-lg-6 d-none d-lg-block pb-0 position-relative">
            <div class="error-visual-container position-relative h-100 p-0 overflow-hidden">
                <img src="<?php echo url('assets/images/error_404.png'); ?>" alt="Digital Void 404"
                    class="img-fluid animate-float w-100 h-100 position-absolute top-0 start-0" style="object-fit: cover; opacity: 0.8; z-index: 1;">
                
                <div class="glass-mask position-absolute w-100 h-100 top-0 start-0"
                    style="background: linear-gradient(90deg, transparent 0%, rgba(10,11,14,0.6) 100%); z-index: 3;">
                </div>

                <!-- Relocated 404 Number -->
                <div class="position-absolute bottom-0 end-0 opacity-25 select-none pe-none p-5 text-end"
                    style="color: var(--vzl-primary); z-index: 4; transform: translateY(-10px) translateX(-10px);">
                    <div style="font-size: 8rem; font-weight: 900; line-height: 0.7;">404</div>
                    <div
                        style="font-size: 2rem; font-weight: 700; letter-spacing: 0.5rem; text-transform: uppercase; margin-top: -0.5rem;">
                        Error</div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="col-lg-6 p-4 p-md-5 d-flex align-items-center">
            <div class="error-content text-start w-100">
                <span
                    class="vzl-section-subtitle mb-3 d-inline-block animate-slide-in px-3 py-2 rounded-pill bg-white-5 border border-white-10 uppercase x-small fw-black tracking-widest"
                    style="color: var(--vzl-primary) !important;">
                    Error de Arquitectura
                </span>
                <h1 class="vzl-section-title animate-fade-in mb-3">
                    Página no <span class="vzl-text-gradient-vibrant">Encontrada</span>
                </h1>
                <p class="text-white-50 mt-4 mb-5 max-w-400 animate-fade-in" style="font-size: 1.05rem; line-height: 1.6;">
                    La página que intentas acceder no existe, la ruta es incorrecta o fue desplazada de nuestra infraestructura digital.
                </p>

                <div class="d-flex gap-3 flex-column flex-sm-row animate-slide-up">
                    <a href="<?php echo url('/'); ?>"
                        class="btn btn-primary px-4 py-3 fw-bold uppercase tracking-widest d-flex align-items-center justify-content-center gap-2 flex-grow-1 shadow-gold">
                        <span class="material-symbols-outlined">home</span>
                        Volver al Inicio
                    </a>
                    <button onclick="window.history.back()"
                        class="btn btn-outline-white px-4 py-3 small fw-bold uppercase d-flex align-items-center justify-content-center gap-2 flex-grow-1">
                        <span class="material-symbols-outlined">arrow_back</span>
                        Regresar
                    </button>
                </div>

                <div
                    class="mt-5 pt-5 border-top border-white-10 text-white-50 d-flex flex-wrap align-items-center gap-4 animate-fade-in-slow">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined fs-6 text-primary">security</span>
                        <span class="x-small uppercase tracking-widest fw-bold">Audit Secure</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined fs-6 text-primary">hub</span>
                        <span class="x-small uppercase tracking-widest fw-bold">Nodes Verified</span>
                    </div>
                </div>
            </div>
        </div>
        </div> <!-- Closing glass-morphism -->
    </div>
</div>

<style>
    .vzl-section-subtitle {
        color: var(--vzl-secondary);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        font-size: 0.85rem;
    }
    .vzl-section-title {
        font-size: 3.5rem;
        font-weight: 900;
        color: white;
        line-height: 1.1;
        letter-spacing: -1px;
    }
    .vzl-text-gradient-vibrant {
        background: linear-gradient(135deg, var(--vzl-primary) 0%, var(--vzl-secondary) 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .cta-parallax-bg {
        background: linear-gradient(rgba(10, 10, 10, 0.7), rgba(10, 10, 10, 0.7)), url('<?php echo url('assets/images/hero_background.png'); ?>');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .border-cyan-glow {
        border: 1px solid color-mix(in srgb, var(--vzl-primary) 20%, transparent);
        box-shadow: 0 0 50px rgba(0, 0, 0, 0.5), 0 0 30px color-mix(in srgb, var(--vzl-primary) 15%, transparent);
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    .animate-slide-in {
        animation: slideIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .animate-fade-in {
        animation: fadeIn 1.2s ease;
    }

    .animate-fade-in-slow {
        animation: fadeIn 2s ease;
    }

    .animate-slide-up {
        animation: slideUp 1s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .max-w-400 {
        max-width: 400px;
    }

    @media (max-width: 991px) {
        .display-1 {
            font-size: 3.5rem;
        }
    }
</style>
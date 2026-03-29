<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center p-0 overflow-hidden position-relative cta-parallax-bg">
    <!-- Abstract Background Ambient Light -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-20"
        style="background: radial-gradient(circle at 50% 50%, rgba(0,242,255,0.15) 0%, transparent 70%);"></div>

    <div class="row g-0 w-100 align-items-center justify-content-center position-relative" style="z-index: 2;">
        <div class="col-11 col-xl-10 glass-morphism rounded-5 border-white-10 shadow-2xl p-0 d-flex flex-wrap overflow-hidden">
        <!-- Image Section -->
        <div class="col-lg-6 d-none d-lg-block">
            <div class="error-visual-container position-relative h-100 p-5 d-flex align-items-center">
                <div class="glass-mask position-absolute w-100 h-100 top-0 start-0"
                    style="background: linear-gradient(90deg, transparent 0%, transparent 80%, rgba(10,11,14,0.5) 100%); z-index: 3;">
                </div>
                <img src="<?php echo url('assets/images/error_404.png'); ?>" alt="Digital Void 404"
                    class="img-fluid rounded-5 shadow-lg animate-float" style="object-fit: cover; opacity: 0.9;">

                <!-- Relocated 404 Number -->
                <div class="position-absolute bottom-0 end-0 opacity-25 select-none pe-none p-4 text-end"
                    style="color: var(--tech-blue); z-index: 4; transform: translateY(-10px) translateX(-10px);">
                    <div style="font-size: 8rem; font-weight: 900; line-height: 0.7;">404</div>
                    <div
                        style="font-size: 2rem; font-weight: 700; letter-spacing: 0.5rem; text-transform: uppercase; margin-top: -0.5rem;">
                        Error</div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="col-lg-6 p-5 d-flex align-items-center">
            <div class="error-content text-start">
                <span
                    class="badge bg-primary text-white px-3 py-2 rounded-pill fw-bold mb-3 small tracking-widest uppercase animate-slide-in" style="box-shadow: 0 0 15px rgba(0,242,255,0.3);">Error
                    404</span>
                <h1 class="display-1 fw-black text-white mb-0 tracking-tighter">
                    PÁGINA NO <span class="text-gradient-404">ENCONTRADA</span>
                </h1>
                <p class="lead text-white-50 mt-4 mb-5 max-w-400 animate-fade-in">
                    La página que intentas acceder no existe, la ruta es incorrecta o fue desplazada de la arquitectura.
                </p>

                <div class="d-flex gap-3 flex-wrap animate-slide-up">
                    <a href="<?php echo url('/'); ?>"
                        class="btn btn-hero-cyan-outline px-5 py-3 rounded-pill fw-bold d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">home</span>
                        Volver al Inicio
                    </a>
                    <button onclick="window.history.back()"
                        class="btn btn-hero-pink px-4 py-3 rounded-pill fw-bold d-flex align-items-center gap-2">
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
    .text-gradient-404 {
        background: linear-gradient(to right, #00f2ff, #ec4899);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .btn-hero-cyan-outline {
        background: transparent;
        color: #00f2ff;
        font-weight: 700;
        border-radius: 50rem;
        padding: 0.9rem 2.5rem;
        font-size: 0.75rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        text-decoration: none;
        border: 1px solid rgba(0, 242, 255, 0.4);
        transition: all 0.3s ease;
    }
    .btn-hero-cyan-outline:hover {
        background: rgba(0, 242, 255, 0.15);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 242, 255, 0.3);
        color: #00f2ff;
    }

    .btn-hero-pink {
        background: transparent;
        color: #ec4899;
        font-weight: 700;
        border-radius: 50rem;
        padding: 0.9rem 2.5rem;
        font-size: 0.75rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        text-decoration: none;
        border: 1px solid rgba(236, 72, 153, 0.4);
        transition: all 0.3s ease;
    }
    .btn-hero-pink:hover {
        background: rgba(236, 72, 153, 0.15);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(236, 72, 153, 0.3);
        color: #ec4899;
    }

    .cta-parallax-bg {
        background: linear-gradient(rgba(10, 10, 10, 0.7), rgba(10, 10, 10, 0.7)), url('<?php echo url('assets/images/hero_background.png'); ?>');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .border-cyan-glow {
        border: 1px solid rgba(0, 242, 255, 0.2);
        box-shadow: 0 0 50px rgba(0, 0, 0, 0.5), 0 0 30px rgba(0, 242, 255, 0.15);
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
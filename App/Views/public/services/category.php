<section class="min-vh-60 d-flex align-items-center position-relative overflow-hidden pt-5 pb-5">
    <!-- Parallax Background -->
    <div class="position-absolute top-0 start-0 w-100 h-100 zoom-parallax"
        style="background: linear-gradient(rgba(10, 11, 14, 0.7), rgba(10, 11, 14, 0.9)), url('<?php echo $category['image'] ? url($category['image']) : url('assets/images/hero_fallback.jpg'); ?>') center/cover no-repeat; transform: scale(1.1); z-index: 0;">
    </div>

    <div class="container pt-5 position-relative" style="z-index: 1;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>"
                        class="text-primary text-decoration-none small fw-bold uppercase px-0 shadow-sm">Inicio</a></li>
                <li class="breadcrumb-item active text-white-50 small fw-bold uppercase" aria-current="page">
                    <?php echo $category['name']; ?>
                </li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div
                        class="rounded-circle bg-midnight text-accent p-3 backdrop-blur border border-white-10 flex-shrink-0 icon-container">
                        <span class="material-symbols-outlined fs-1">
                            <?php echo $category['icon']; ?>
                        </span>
                    </div>
                    <h1 class="vzl-h1-premium text-white mb-0 text-gradient text-nowrap">
                        <?php echo $category['name']; ?>
                    </h1>
                </div>
                <p class="text-white-50 overflow-hidden" style="max-width: 600px; line-height: 1.6;">
                    <span
                        class="d-block bg-primary bg-opacity-10 p-3 rounded-4 border-start border-primary border-4 backdrop-blur shadow-2xl small">
                        <?php echo $category['description']; ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-deep-black">
    <div class="container py-5">
        <div class="row g-4">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6">
                    <div class="card bg-midnight border-white-10 h-100 overflow-hidden hover-lift transition-all">
                        <div class="row g-0 h-100">
                            <div
                                class="col-md-3 bg-midnight d-flex align-items-center justify-content-center p-3 border-end border-white-5 icon-container">
                                <span class="material-symbols-outlined text-accent"
                                    style="font-size: 3.5rem; filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.2));">
                                    <?php echo $service['icon'] ?? 'analytics'; ?>
                                </span>
                            </div>
                            <div class="col-md-9 p-3 d-flex flex-column">
                                <div class="mb-3">
                                    <h4 class="vzl-card-title mb-1 text-gradient">
                                        <?php echo $service['name']; ?>
                                    </h4>
                                    <p class="vzl-card-text mb-0">
                                        <?php echo $service['short_description']; ?>
                                    </p>
                                </div>
                                <div class="mt-auto d-flex gap-2 flex-wrap">
                                    <a href="<?php echo url('service/detail/' . $service['slug']); ?>"
                                        class="btn btn-outline-white px-4 py-2 small fw-bold uppercase">Ver
                                        Planes</a>
                                    <a href="<?php echo url('ticket/request?service=' . $service['id']); ?>"
                                        class="btn vzl-btn-glow-magenta px-4 py-2 fw-bold uppercase">Contratar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
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

    .card.hover-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card.hover-lift:hover {
        border-color: rgba(212, 175, 55, 0.5) !important;
        box-shadow: 0 10px 30px -10px rgba(212, 175, 55, 0.3) !important;
        transform: translateY(-8px);
    }
</style>
<section class="min-vh-60 d-flex align-items-center position-relative overflow-hidden pt-5 pb-5">
    <!-- Brand Background -->
    <div class="position-absolute top-0 start-0 w-100 h-100 zoom-parallax"
        style="background: linear-gradient(rgba(10, 11, 14, 0.9), rgba(10, 11, 14, 0.95)), url('<?php echo url('assets/images/hero_background.png'); ?>') center/cover no-repeat; z-index: 0;">
    </div>

    <div class="container pt-5 text-center position-relative" style="z-index: 1;">
        <h5 class="text-primary small fw-bold mb-3 text-uppercase tracking-widest">Explora Nuestro Conocimiento</h5>
        <h1 class="display-5 fw-black text-white mb-4 tracking-tighter">Insights & <span class="text-gradient">Tech
                Blog</span></h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 700px;">
            Tendencias, guías y análisis sobre ingeniería de datos, IA y el futuro de la tecnología enterprise.
        </p>
    </div>
</section>

<!-- Category Filters Bar -->
<section class="py-4 bg-deep-black border-bottom border-white-10">
    <div class="container text-center">
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <button
                class="btn rounded-pill px-4 py-2 uppercase fw-bold tracking-widest filter-btn <?php echo !isset($categoryFilter) ? 'active' : ''; ?>"
                onclick="window.location.href='<?php echo url('blog'); ?>'">Todos</button>
            <?php foreach ($categories as $bCat): ?>
                <button
                    class="btn rounded-pill px-4 py-2 uppercase fw-bold tracking-widest filter-btn <?php echo (isset($categoryFilter) && $categoryFilter == $bCat['slug']) ? 'active' : ''; ?>"
                    onclick="window.location.href='<?php echo url('blog/category/' . $bCat['slug']); ?>'">
                    <?php echo $bCat['name']; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-deep-black">
    <div class="container py-4">
        <?php if (empty($posts)): ?>
            <div class="glass-morphism p-5 text-center rounded-4 border-white-10">
                <span class="material-symbols-outlined display-1 text-white-10 mb-3">auto_stories</span>
                <h3 class="text-white">Aún no hay publicaciones</h3>
                <p class="text-white-50">Pronto compartiremos contenido increíble en esta categoría.</p>
                <a href="<?php echo url('blog'); ?>"
                    class="btn vzl-btn-glow-magenta px-5 py-3 fw-bold uppercase tracking-widest mt-3">Seguir leyendo</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 col-lg-4">
                        <article
                            class="card h-100 border-white-10 bg-midnight overflow-hidden hover-lift transition-all rounded-4 shadow-lg">
                            <div class="position-relative" style="height: 220px; overflow: hidden;">
                                <?php if ($post['featured_image']): ?>
                                    <img src="<?php echo url($post['featured_image']); ?>" class="w-100 h-100 object-fit-cover"
                                        alt="<?php echo $post['title']; ?>">
                                <?php else: ?>
                                    <div class="w-100 h-100 bg-steel d-flex align-items-center justify-content-center">
                                        <span class="material-symbols-outlined display-3 text-accent opacity-20">image</span>
                                    </div>
                                <?php endif; ?>
                                <div class="position-absolute top-0 start-0 p-3">
                                    <span class="badge rounded-pill fw-bold uppercase px-3 py-2"
                                        style="background-color: <?php echo $post['category_color'] ?? '#3B82F6'; ?>; font-size: 0.65rem;">
                                        <?php echo $post['category_name']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <span
                                    class="text-white-50 x-small fw-bold uppercase tracking-widest mb-3 d-flex align-items-center gap-2">
                                    <span class="material-symbols-outlined fs-6 text-primary">calendar_today</span>
                                    <?php echo date('d M, Y', strtotime($post['published_at'])); ?>
                                </span>
                                <h4 class="h5 text-white fw-bold mb-3 ls-tight">
                                    <?php echo $post['title']; ?>
                                </h4>
                                <p class="text-white-50 small mb-4 line-clamp-3 flex-grow-1">
                                    <?php echo $post['excerpt']; ?>
                                </p>
                                <div
                                    class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top border-white-5">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-midnight d-flex align-items-center justify-content-center text-primary fw-bold small"
                                            style="width: 28px; height: 28px; font-size: 0.7rem;">
                                            <?php echo strtoupper(substr($post['author_name'], 0, 1)); ?>
                                        </div>
                                        <span class="text-white-50 x-small fw-bold"><?php echo $post['author_name']; ?></span>
                                    </div>
                                    <a href="<?php echo url('blog/post/' . $post['slug']); ?>"
                                        class="text-primary text-decoration-none small fw-bold uppercase tracking-widest d-flex align-items-center gap-2 hover-white transition-all">
                                        Leer <span class="material-symbols-outlined fs-6">east</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <nav class="mt-5 pt-5" aria-label="Navegación de blog">
                    <ul class="pagination justify-content-center gap-2">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $currentPage == $i ? 'active' : ''; ?>">
                                <a class="page-link border-white-10 <?php echo $currentPage == $i ? 'bg-primary text-deep-black border-primary' : 'bg-white-5 text-white hover-bg-primary hover-text-black'; ?> rounded-pill px-4 fw-bold"
                                    href="<?php echo url('blog?page=' . $i); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
    .zoom-parallax {
        animation: subtleZoom 20s infinite alternate linear;
    }

    @keyframes subtleZoom {
        from {
            transform: scale(1.05);
        }

        to {
            transform: scale(1.15);
        }
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .ls-tight {
        letter-spacing: -0.01em;
    }

    .tracking-tighter {
        letter-spacing: -2px;
    }

    .pagination .page-link {
        transition: all 0.3s ease;
    }

    .pagination .page-link:hover {
        transform: translateY(-3px);
    }

    /* Filter Buttons Matching Pillar Cards */
    .filter-btn {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: rgba(255, 255, 255, 0.5);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        font-size: 0.75rem;
    }

    .filter-btn:hover {
        transform: translateY(-3px);
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--tech-blue) !important;
        color: white !important;
    }

    .filter-btn.active {
        background-color: var(--steel-blue) !important;
        border-color: var(--steel-blue) !important;
        color: white !important;
        box-shadow: 0 0 15px rgba(51, 101, 138, 0.4);
        transform: scale(1.05);
    }
</style>
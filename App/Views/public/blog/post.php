<?php if ($post['featured_image']): ?>
    <section class="position-relative overflow-hidden mt-5" style="height: 50vh;">
        <!-- Parallax Background -->
        <div class="position-absolute top-0 start-0 w-100 h-100 zoom-parallax">
            <img src="<?php echo url($post['featured_image']); ?>" class="w-100 h-100 object-fit-cover"
                alt="<?php echo $post['title']; ?>">
            <div
                class="position-absolute w-100 h-100 top-0 start-0 bg-gradient-to-t from-deep-black via-transparent to-transparent">
            </div>
        </div>
    </section>
<?php endif; ?>

<article class="bg-deep-black">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header Info -->
                <header class="text-center mb-5" data-aos="fade-up">
                    <nav aria-label="breadcrumb" class="d-flex justify-content-center mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo url('blog'); ?>"
                                    class="text-primary text-decoration-none small fw-bold uppercase">Blog</a></li>
                            <li class="breadcrumb-item active text-white-50 small fw-bold uppercase"
                                aria-current="page"><?php echo $post['category_name']; ?></li>
                        </ol>
                    </nav>

                    <h1 class="display-3 fw-black text-white mb-4 tracking-tighter">
                        <?php echo $post['title']; ?>
                    </h1>

                    <div
                        class="d-flex align-items-center justify-content-center flex-wrap gap-4 text-white-50 x-small fw-bold uppercase tracking-widest">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined fs-6 text-primary">person</span>
                            <span class="text-white"><?php echo $post['author_name']; ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined fs-6 text-primary">calendar_today</span>
                            <span
                                class="text-white"><?php echo date('d M, Y', strtotime($post['published_at'])); ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined fs-6 text-primary">visibility</span>
                            <span class="text-white"><?php echo $post['views_count']; ?> Vistas</span>
                        </div>
                    </div>
                </header>

                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <!-- Post Content -->
                        <div class="text-white-50 fs-5 mb-5 lh-lg blog-content" data-aos="fade-up">
                            <?php echo $post['content']; ?>
                        </div>

                        <!-- Footer / Share -->
                        <div
                            class="border-top border-white-10 pt-5 mt-5 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-4">
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-white small fw-bold uppercase tracking-widest">Compartir:</span>
                                <div class="d-flex gap-2">
                                    <a href="#"
                                        class="btn btn-outline-white-10 btn-sm rounded-circle p-2 hover-bg-primary transition-all">
                                        <span class="material-symbols-outlined fs-6">share</span>
                                    </a>
                                </div>
                            </div>
                            <a href="<?php echo url('blog'); ?>"
                                class="btn btn-primary px-5 py-3 shadow-gold fw-bold uppercase tracking-widest">
                                Volver al Listado
                            </a>
                        </div>

                        <!-- Comments Section -->
                        <section class="mt-5 pt-5">
                            <h3 class="text-white fw-bold mb-5 d-flex align-items-center gap-3">
                                <span class="material-symbols-outlined fs-2 text-primary">forum</span>
                                Comentarios (<?php echo count($comments); ?>)
                            </h3>

                            <?php if (empty($comments)): ?>
                                <div class="glass-morphism p-5 rounded-4 text-center mb-5 border-white-10">
                                    <p class="text-white-50 mb-0">No hay comentarios aún. ¡Sé el primero en opinar!</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4 mb-5">
                                    <?php foreach ($comments as $comment): ?>
                                        <div
                                            class="glass-morphism p-4 rounded-4 border-white-10 mb-3 transition-all hover-white-5">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-circle bg-midnight d-flex align-items-center justify-content-center text-primary fw-bold"
                                                        style="width: 45px; height: 45px;">
                                                        <?php echo strtoupper(substr($comment['author_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <h5 class="text-white h6 mb-0"><?php echo $comment['author_name']; ?>
                                                        </h5>
                                                        <span
                                                            class="text-white-50 x-small"><?php echo date('d M, Y', strtotime($comment['created_at'])); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-white-50 small mb-0"><?php echo $comment['content']; ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Comment Form Toggle -->
                            <div class="text-center mb-5" id="commentBtnContainer">
                                <button
                                    onclick="document.getElementById('commentForm').classList.toggle('d-none'); this.parentElement.classList.add('d-none')"
                                    class="btn btn-outline-primary px-5 py-3 fw-bold uppercase tracking-widest d-inline-flex align-items-center gap-2">
                                    <span class="material-symbols-outlined">add_comment</span>
                                    Comentar
                                </button>
                            </div>

                            <!-- Comment Form -->
                            <div class="glass-morphism p-5 rounded-4 border-primary border-opacity-25 d-none mb-5"
                                id="commentForm">
                                <h4 class="text-white fw-bold mb-4">Escribe tu opinión</h4>
                                <form action="<?php echo url('blog/comment'); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <?php
                                    $user = \Core\Auth::user();
                                    $userName = $user ? $user['name'] : '';
                                    $userEmail = $user ? $user['email'] : '';
                                    ?>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label
                                                class="text-white-50 small mb-2 uppercase tracking-widest fw-bold">Nombre</label>
                                            <input type="text" name="name"
                                                class="form-control bg-steel border-white-10 text-white p-3"
                                                value="<?php echo $userName; ?>" <?php echo $user ? 'readonly' : 'required'; ?>>
                                        </div>
                                        <div class="col-md-6">
                                            <label
                                                class="text-white-50 small mb-2 uppercase tracking-widest fw-bold">Email</label>
                                            <input type="email" name="email"
                                                class="form-control bg-steel border-white-10 text-white p-3"
                                                value="<?php echo $userEmail; ?>" <?php echo $user ? 'readonly' : 'required'; ?>>
                                        </div>
                                        <div class="col-12">
                                            <label
                                                class="text-white-50 small mb-2 uppercase tracking-widest fw-bold">Comentario</label>
                                            <textarea name="content"
                                                class="form-control bg-steel border-white-10 text-white p-3" rows="5"
                                                required placeholder="Comparte tus pensamientos..."></textarea>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit"
                                                class="btn btn-primary px-5 py-3 fw-bold uppercase tracking-widest mt-2 shadow-gold">
                                                Publicar Comentario
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>

<style>
    .zoom-parallax img {
        animation: subtleZoom 20s infinite alternate linear;
    }

    @keyframes subtleZoom {
        from {
            transform: scale(1);
        }

        to {
            transform: scale(1.1);
        }
    }

    .tracking-tighter {
        letter-spacing: -2px;
    }

    .blog-content {
        color: rgba(255, 255, 255, 0.7);
        letter-spacing: 0.01em;
    }

    .blog-content p {
        margin-bottom: 2rem;
    }

    .blog-content h2,
    .blog-content h3 {
        color: white;
        margin-top: 3rem;
        margin-bottom: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .blog-content strong {
        color: var(--vzl-color-gold, #D4AF37);
    }

    .breadcrumb-item+.breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.3);
    }

    .hover-white-5:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
</style>
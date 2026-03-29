<div class="row justify-content-center">
    <div class="col-lg-9">
        <a href="<?php echo url('admin/blog'); ?>"
            class="text-white-50 text-decoration-none small d-inline-flex align-items-center gap-2 hover-gold mb-3 transition-all">
            <span class="material-symbols-outlined fs-6">arrow_back</span> Volver a la lista
        </a>
        <h2 class="text-white fw-black mb-4">Editar <span class="text-primary">Publicación</span></h2>

        <form action="<?php echo url('admin/blog/update'); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="glass-morphism p-4 rounded-5 border-white-10 bg-white-5 shadow-2xl">
                        <div class="mb-4">
                            <label class="text-white-50 x-small uppercase fw-bold mb-2">Título del Artículo</label>
                            <input type="text" name="title"
                                class="form-control bg-steel border-white-10 text-white p-3 fs-4 fw-bold"
                                value="<?php echo $post['title']; ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="text-white-50 x-small uppercase fw-bold mb-2">Extracto (Resumen corto)</label>
                            <textarea name="excerpt" class="form-control bg-steel border-white-10 text-white p-3"
                                rows="3" required><?php echo $post['excerpt']; ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="text-white-50 x-small uppercase fw-bold mb-2">Imagen Destacada</label>
                            <?php if ($post['featured_image']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo url($post['featured_image']); ?>" class="rounded border-white-10"
                                        style="max-height: 100px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="featured_image"
                                class="form-control bg-steel border-white-10 text-white p-3">
                        </div>
                        <div class="mb-0">
                            <label class="text-white-50 x-small uppercase fw-bold mb-2">Contenido (HTML
                                soportado)</label>
                            <textarea name="content" class="form-control bg-steel border-white-10 text-white p-3"
                                rows="15" required><?php echo $post['content']; ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="glass-morphism p-4 rounded-5 border-white-10 sticky-top" style="top: 100px;">
                        <h5 class="text-white small fw-bold uppercase tracking-widest mb-4">Configuración</h5>

                        <div class="mb-4">
                            <label class="text-white-50 x-small uppercase fw-bold mb-2">Categoría</label>
                            <select name="category_id" class="form-select bg-steel border-white-10 text-white p-3">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $post['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo $cat['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="text-white-50 x-small uppercase fw-bold mb-2">Estado</label>
                            <select name="status" class="form-select bg-steel border-white-10 text-white p-3">
                                <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>Borrador
                                </option>
                                <option value="published" <?php echo $post['status'] == 'published' ? 'selected' : ''; ?>>
                                    Publicado</option>
                            </select>
                        </div>

                        <div class="p-3 bg-white-5 rounded-4 border-white-10 mb-4 text-center">
                            <p class="text-white-50 x-small mb-1 uppercase fw-bold tracking-widest">Estadísticas</p>
                            <div class="d-flex justify-content-center gap-3">
                                <div class="text-white">
                                    <span class="material-symbols-outlined fs-6 align-middle">visibility</span>
                                    <span class="small">
                                        <?php echo $post['views_count']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-primary w-100 py-3 fw-black uppercase tracking-widest shadow-gold">Guardar
                            Cambios</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
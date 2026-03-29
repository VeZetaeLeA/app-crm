<div class="row g-4">
    <div class="col-12 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h2 class="text-white fw-black mb-1">Centro Editorial ✍️</h2>
            <p class="text-white-50">Gestiona las publicaciones y artículos de <?= \Core\Config::get('business.company_name') ?>.</p>
        </div>
        <a href="<?php echo url('admin/blog/create'); ?>"
            class="btn btn-primary btn-sm px-4 fw-bold rounded-pill shadow-gold">Nueva
            Entrada</a>
    </div>

    <div class="col-12">
        <div class="glass-morphism rounded-5 border-white-10 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-deep-black">
                        <tr class="x-small uppercase text-white-50 tracking-widest">
                            <th class="p-4 border-0">Publicación</th>
                            <th class="p-4 border-0">Categoría</th>
                            <th class="p-4 border-0">Estado</th>
                            <th class="p-4 border-0">Vistas</th>
                            <th class="p-4 border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $p): ?>
                            <tr>
                                <td class="p-4">
                                    <div class="fw-bold text-white">
                                        <?php echo $p['title']; ?>
                                    </div>
                                    <div class="x-small text-white-50 mt-1">Por:
                                        <?php echo $p['author_name']; ?> •
                                        <?php echo date('d/m/Y', strtotime($p['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="badge border border-white-10 text-white-50 px-3 py-2 uppercase x-small">
                                        <?php echo $p['category_name']; ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="badge bg-<?php echo $p['status'] == 'published' ? 'success' : 'warning'; ?> bg-opacity-10 text-<?php echo $p['status'] == 'published' ? 'success' : 'warning'; ?> border border-<?php echo $p['status'] == 'published' ? 'success' : 'warning'; ?> border-opacity-25 px-3 py-1 uppercase x-small">
                                        <?php echo translateStatus($p['status']); ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="text-white-50 small">
                                        <?php echo $p['views_count']; ?>
                                    </span>
                                </td>
                                <td class="p-4 text-end">
                                    <div class="btn-group">
                                        <a href="<?php echo url('admin/blog/edit/' . $p['id']); ?>"
                                            class="btn btn-outline-primary btn-sm rounded-3 me-2"><span
                                                class="material-symbols-outlined fs-6 align-middle">edit</span></a>
                                        <a href="<?php echo url('admin/blog/delete/' . $p['id']); ?>"
                                            class="btn btn-outline-danger btn-sm rounded-3"
                                            onclick="return confirm('¿Eliminar permanentemente?')"><span
                                                class="material-symbols-outlined fs-6 align-middle">delete</span></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="<?php echo url('project/workspace'); ?>"
                    class="text-accent x-small fw-bold text-decoration-none d-flex align-items-center gap-1 mb-2">
                    <span class="material-symbols-outlined fs-6">arrow_back</span> Volver
                </a>
                <h2 class="text-white fw-black mb-1">
                    <?php echo $service['name']; ?>
                </h2>
                <p class="text-white-50 small">Cliente: <span class="text-white">
                        <?php echo $service['client_name']; ?>
                    </span> | Plan: <span class="text-gold">
                        <?php echo $service['plan_name']; ?>
                    </span></p>
            </div>
        </div>

        <div class="row g-3">
            <?php if (empty($deliverables)): ?>
                <div class="col-12">
                    <div class="glass-morphism p-5 text-center rounded-5">
                        <span class="material-symbols-outlined display-1 text-white-10 mb-3">folder_open</span>
                        <h4 class="text-white fw-bold">No hay entregables</h4>
                        <p class="text-white-50">Sube el primer archivo usando el formulario lateral.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($deliverables as $file): ?>
                    <div class="col-md-6 col-xxl-4">
                        <div
                            class="p-3 rounded-4 bg-steel border border-white-5 h-100 hover-lift transition-all position-relative">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-3 bg-white-5 p-2 d-flex align-items-center justify-content-center text-accent"
                                    style="width: 48px; height: 48px;">
                                    <span class="material-symbols-outlined fs-2">
                                        <?php
                                        switch ($file['file_type']) {
                                            case 'document':
                                                echo 'description';
                                                break;
                                            case 'code':
                                                echo 'terminal';
                                                break;
                                            case 'data':
                                                echo 'database';
                                                break;
                                            case 'image':
                                                echo 'image';
                                                break;
                                            default:
                                                echo 'draft';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="text-white fw-bold mb-0 text-truncate"><?php echo $file['title']; ?></h6>
                                    <span class="text-white-50 x-small">v<?php echo $file['version']; ?> |
                                        <?php echo number_format($file['file_size'] / 1024, 1); ?> KB</span>
                                </div>
                            </div>
                            <p class="text-white-50 x-small mb-4 line-clamp-2"><?php echo $file['description']; ?></p>

                            <div class="d-flex gap-2">
                                <a href="<?php echo url('project/download/' . $file['id']); ?>"
                                    class="btn btn-outline-light btn-sm flex-grow-1 rounded-pill border-white-10 fw-bold">
                                    <span class="material-symbols-outlined fs-6 align-middle me-1">download</span> Descargar
                                </a>
                                <a href="<?php echo url('project/delete/' . $file['id']); ?>"
                                    class="btn btn-outline-danger btn-sm rounded-circle border-white-10"
                                    onclick="return confirm('¿Eliminar este entregable?')"
                                    style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <span class="material-symbols-outlined fs-6">delete</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <style>
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .hover-lift:hover {
                transform: translateY(-5px);
                border-color: rgba(212, 175, 55, 0.4) !important;
                background: rgba(255, 255, 255, 0.05);
            }
        </style>
    </div>

    <div class="col-lg-4">
        <!-- Project Scope Management (PRD v1.0) -->
        <div class="glass-morphism p-4 rounded-5 border-white-10 mb-4">
            <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">analytics</span> Alcance del Proyecto
            </h5>
            <p class="text-white-50 x-small mb-4">Define el número total de entregables para habilitar la barra de
                progreso al cliente.</p>

            <form action="<?php echo url('project/updateScope'); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="active_service_id" value="<?php echo $service['id']; ?>">
                <div class="d-flex gap-2">
                    <div class="flex-grow-1">
                        <label class="text-white-50 x-small fw-bold uppercase tracking-widest d-block mb-1">Total
                            Entregables</label>
                        <input type="number" name="total_deliverables"
                            class="form-control bg-steel border-white-10 text-white"
                            value="<?php echo $service['total_deliverables']; ?>" min="0" required>
                    </div>
                    <div class="align-self-end">
                        <button type="submit"
                            class="btn btn-outline-primary btn-sm py-2 px-3 rounded-3">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="glass-morphism p-4 rounded-5 border-white-10 sticky-top" style="top: 20px;">
            <h5 class="text-white fw-bold mb-4">Cargar Nuevo Entregable</h5>
            <form action="<?php echo url('project/upload'); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="active_service_id" value="<?php echo $service['id']; ?>">

                <div class="mb-3">
                    <label class="form-label text-white-50 x-small fw-bold">Título del Archivo</label>
                    <input type="text" name="title" class="form-control bg-steel border-white-10 text-white"
                        placeholder="Ej: Reporte de Calidad de Datos" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white-50 x-small fw-bold">Descripción Corta</label>
                    <textarea name="description" class="form-control bg-steel border-white-10 text-white" rows="2"
                        placeholder="Detalle qué incluye este entregable..."></textarea>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label text-white-50 x-small fw-bold">Versión</label>
                        <input type="text" name="version" class="form-control bg-steel border-white-10 text-white"
                            placeholder="1.0" value="1.0">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-white-50 x-small fw-bold">Tipo</label>
                        <select name="file_type" class="form-select bg-steel border-white-10 text-white x-small">
                            <option value="document">Documento</option>
                            <option value="data">Base de Datos / CSV</option>
                            <option value="code">Código / Script</option>
                            <option value="image">Imagen / Diagrama</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-white-50 x-small fw-bold">Archivo del Proyecto</label>
                    <input type="file" name="deliverable" class="form-control bg-steel border-white-10 text-white"
                        required>
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                    <span class="material-symbols-outlined fs-6 align-middle me-1">cloud_upload</span> Subir Entregable
                </button>
            </form>
        </div>
    </div>
</div>
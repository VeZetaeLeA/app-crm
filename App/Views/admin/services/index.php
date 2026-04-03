<div class="row g-4 mb-4">
    <div class="col-12 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h2 class="text-white fw-black mb-1">Catálogo de Servicios 🛠️</h2>
            <p class="text-white-50">Gestiona la oferta comercial y los planes de <?= \Core\Config::get('business.company_name') ?>.</p>
        </div>

        <?php
        $curated_icons = [
            'database' => 'Base de Datos',
            'analytics' => 'Analítica',
            'bar_chart' => 'Gráficos',
            'query_stats' => 'Consultas/Estadísticas',
            'insights' => 'Insights',
            'dataset' => 'Dataset',
            'storage' => 'Almacenamiento',
            'smart_toy' => 'IA / Robot',
            'memory' => 'Memoria / Hardware',
            'psychology' => 'Algoritmos / Mente',
            'bolt' => 'Velocidad / Energía',
            'auto_awesome' => 'Magia / Automatización',
            'terminal' => 'Terminal / Consola',
            'code' => 'Código',
            'engineering' => 'Ingeniería',
            'hub' => 'Hub de Datos',
            'cloud' => 'Cloud',
            'shield' => 'Seguridad',
            'trending_up' => 'Crecimiento',
            'account_tree' => 'Flujos / Jerarquía',
            'rocket' => 'Lanzamiento',
            'speed' => 'Rendimiento',
            'folder' => 'Carpeta',
            'settings' => 'Configuración',
            'groups' => 'Usuarios / Grupos',
            'inventory_2' => 'Inventario/Archivos'
        ];
        ?>

        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm px-4 fw-bold rounded-pill" data-bs-toggle="modal"
                data-bs-target="#modalCategory">Nueva Categoría</button>
            <button class="btn btn-primary btn-sm px-4 fw-bold rounded-pill shadow-gold" data-bs-toggle="modal"
                data-bs-target="#modalService">Nuevo Servicio</button>
        </div>
    </div>

    <!-- Categorías -->
    <div class="col-12">
        <div class="glass-morphism p-3 p-md-4 rounded-5 border-white-10">
            <h2 class="text-white h6 fw-black uppercase tracking-widest mb-4">Categorías Activas</h2>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($categories as $cat): ?>
                    <span
                        class="badge border border-white-10 text-white p-2 p-md-3 rounded-4 d-flex align-items-center gap-2 gap-md-3">
                        <span class="material-symbols-outlined text-primary fs-5">folder</span>
                        <span class="small fw-bold"><?php echo $cat['name']; ?></span>
                        <a href="javascript:void(0)" class="text-white-50 hover-gold transition-all edit-category-btn"
                            data-id="<?php echo $cat['id']; ?>" data-name="<?php echo $cat['name']; ?>"
                            data-slug="<?php echo $cat['slug']; ?>" data-icon="<?php echo $cat['icon']; ?>"
                            data-description="<?php echo $cat['description']; ?>" data-image="<?php echo $cat['image']; ?>"
                            data-active="<?php echo $cat['is_active']; ?>">
                            <span class="material-symbols-outlined fs-6">edit</span>
                        </a>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Filtro Dinámico (Multi-selección) -->
    <div class="col-12 mt-4">
        <div class="d-flex align-items-center gap-3 mb-3 px-2">
            <span class="text-white-50 x-small uppercase fw-black tracking-widest">Filtrar por Pilares:</span>
            
            <div class="dropdown">
                <button class="btn btn-midnight border-white-10 text-white rounded-pill px-4 py-2 d-flex align-items-center gap-2 dropdown-toggle shadow-sm" 
                        type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                    <span class="material-symbols-outlined fs-5">filter_alt</span>
                    <span id="filter-label">Todas las categorías</span>
                </button>
                <div class="dropdown-menu dropdown-menu-dark glass-morphism border-white-10 p-3 shadow-2xl" aria-labelledby="filterDropdown" style="min-width: 250px;">
                    <div class="form-check mb-2">
                        <input class="form-check-input filter-checkbox" type="checkbox" value="all" id="check-all" checked>
                        <label class="form-check-label text-white small fw-bold" for="check-all">
                            Todas las categorías
                        </label>
                    </div>
                    <div class="dropdown-divider border-white-10"></div>
                    <div id="category-checkbox-list">
                        <?php 
                        $catCounts = [];
                        foreach ($services as $serv) {
                            $catCounts[$serv['category_id']] = ($catCounts[$serv['category_id']] ?? 0) + 1;
                        }
                        foreach ($categories as $cat): 
                        ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input filter-checkbox" type="checkbox" value="<?php echo $cat['id']; ?>" id="check-<?php echo $cat['id']; ?>">
                                <label class="form-check-label text-white-50 small" for="check-<?php echo $cat['id']; ?>">
                                    <?php echo $cat['name']; ?> 
                                    <span class="x-small opacity-50 ms-1">(<?php echo $catCounts[$cat['id']] ?? 0; ?>)</span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button class="btn btn-link text-white-50 x-small p-0 text-decoration-none d-none" id="clear-filters" onclick="resetFilters()">
                Limpiar filtros
            </button>
        </div>
    </div>

    <!-- Listado de Servicios -->
    <div class="col-12">
        <div class="glass-morphism rounded-5 border-white-10 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-deep-black">
                        <tr class="x-small uppercase text-white-50 tracking-widest">
                            <th class="p-4 border-0">Servicio</th>
                            <th class="p-4 border-0">Categoría</th>
                            <th class="p-4 border-0">Icono</th>
                            <th class="p-4 border-0">Estado</th>
                            <th class="p-4 border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $s): ?>
                            <tr class="service-row" data-category-id="<?php echo $s['category_id']; ?>">
                                <td class="p-4">
                                    <div class="fw-bold text-white">
                                        <?php echo $s['name']; ?>
                                    </div>
                                    <div class="x-small text-white-50 mt-1">
                                        <?php echo $s['short_description']; ?>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="badge bg-midnight border border-white-10 text-white-50 px-3 py-2">
                                        <?php echo $s['category_name']; ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="rounded-circle bg-steel d-inline-flex align-items-center justify-content-center text-primary"
                                        style="width: 40px; height: 40px;">
                                        <span class="material-symbols-outlined">
                                            <?php echo $s['icon'] ?? 'bolt'; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <?php if ($s['is_active']): ?>
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1 uppercase x-small">Visible</span>
                                    <?php else: ?>
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1 uppercase x-small">Oculto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?php echo url('admin/services/edit/' . $s['id']); ?>"
                                            class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">Configurar</a>
                                        <button
                                            onclick="confirmDelete(<?php echo $s['id']; ?>, '<?php echo $s['name']; ?>')"
                                            class="btn btn-outline-danger btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px;">
                                            <span class="material-symbols-outlined fs-6">delete</span>
                                        </button>
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

<!-- Modal Nueva Categoría -->
<div class="modal fade" id="modalCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-morphism border-white-10 rounded-5">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="text-white fw-bold mb-0">Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="<?php echo url('admin/services/storeCategory'); ?>" method="POST"
                enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Nombre de Categoría</label>
                        <input type="text" name="name" class="form-control bg-steel border-white-10 text-white p-3"
                            required placeholder="Ej: Inteligencia Artificial">
                    </div>
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Identificador (Slug)</label>
                        <input type="text" name="slug" class="form-control bg-steel border-white-10 text-white p-3"
                            placeholder="Dejar en blanco para autogenerar">
                    </div>
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Icono Material</label>
                        <div class="d-flex gap-2">
                            <select name="icon" class="form-select bg-steel border-white-10 text-white p-3" required
                                onchange="updateIconPreview(this, 'new-cat-icon-preview')">
                                <?php foreach ($curated_icons as $val => $label): ?>
                                    <option value="<?php echo $val; ?>"><?php echo $label; ?> (<?php echo $val; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="bg-white-5 rounded-4 d-flex align-items-center justify-content-center border border-white-10"
                                style="width: 60px;">
                                <span class="material-symbols-outlined text-primary"
                                    id="new-cat-icon-preview">folder</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Imagen de Portada (PNG, JPG, WebP, SVG)</label>
                        <input type="file" name="image" class="form-control bg-steel border-white-10 text-white p-3"
                            accept=".png,.jpg,.jpeg,.webp,.svg">
                    </div>
                    <div class="mb-0">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Descripción</label>
                        <textarea name="description" class="form-control bg-steel border-white-10 text-white p-3"
                            rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit"
                        class="btn btn-primary w-100 py-3 fw-bold uppercase tracking-widest rounded-pill">Crear
                        Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Categoría -->
<div class="modal fade" id="modalEditCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-morphism border-white-10 rounded-5">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="text-white fw-bold mb-0">Editar Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="<?php echo url('admin/services/updateCategory'); ?>" method="POST"
                enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" id="edit-cat-id">
                <input type="hidden" name="existing_image" id="edit-cat-existing-image">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Nombre de Categoría</label>
                        <input type="text" name="name" id="edit-cat-name"
                            class="form-control bg-steel border-white-10 text-white p-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Identificador (Slug)</label>
                        <input type="text" name="slug" id="edit-cat-slug"
                            class="form-control bg-steel border-white-10 text-white p-3"
                            placeholder="Dejar en blanco para mantener">
                    </div>
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Icono Material</label>
                        <div class="d-flex gap-2">
                            <select name="icon" id="edit-cat-icon"
                                class="form-select bg-steel border-white-10 text-white p-3" required
                                onchange="updateIconPreview(this, 'edit-cat-icon-preview')">
                                <?php foreach ($curated_icons as $val => $label): ?>
                                    <option value="<?php echo $val; ?>"><?php echo $label; ?> (<?php echo $val; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="bg-white-5 rounded-4 d-flex align-items-center justify-content-center border border-white-10"
                                style="width: 60px;">
                                <span class="material-symbols-outlined text-primary"
                                    id="edit-cat-icon-preview">folder</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Actualizar Imagen (PNG, JPG, WebP, SVG)</label>
                        <input type="file" name="image" class="form-control bg-steel border-white-10 text-white p-3"
                            accept=".png,.jpg,.jpeg,.webp,.svg">
                        <div id="image-preview-container" class="mt-2 text-center d-none">
                            <p class="x-small text-white-50 mb-1">Imagen actual:</p>
                            <img id="edit-cat-preview" src="" class="rounded border border-white-10"
                                style="max-height: 80px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Descripción</label>
                        <textarea name="description" id="edit-cat-description"
                            class="form-control bg-steel border-white-10 text-white p-3" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <div
                            class="form-check form-switch p-3 bg-white-5 rounded-4 d-flex align-items-center justify-content-between px-4 mt-2">
                            <label class="form-check-label text-white fw-bold mb-0" for="edit-cat-active">Activo</label>
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit-cat-active">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit"
                        class="btn btn-primary w-100 py-3 fw-bold uppercase tracking-widest rounded-pill shadow-gold">Guardar
                        Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nuevo Servicio -->
<div class="modal fade" id="modalService" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-morphism border-white-10 rounded-5">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="text-white fw-bold mb-0">Nuevo Servicio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="<?php echo url('admin/services/storeService'); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Nombre del Servicio</label>
                        <input type="text" name="name" class="form-control bg-steel border-white-10 text-white p-3"
                            required placeholder="Ej: NLP Avanzado">
                    </div>
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Categoría</label>
                        <select name="category_id" class="form-select bg-steel border-white-10 text-white p-3" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Descripción Corta</label>
                        <input type="text" name="short_description"
                            class="form-control bg-steel border-white-10 text-white p-3" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit"
                        class="btn btn-primary w-100 py-3 fw-bold uppercase tracking-widest rounded-pill">Crear y
                        Configurar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        if (confirm('¿Estás seguro de eliminar el servicio "' + name + '"? Esta acción no se puede deshacer.')) {
            window.location.href = window.APP_URL + '/admin/services/deleteService/' + id;
        }
    }

    function updateIconPreview(select, previewId) {
        document.getElementById(previewId).innerText = select.value;
    }

    // Filtrado Dinámico de Servicios (Multi-selección)
    const checkboxes = document.querySelectorAll('.filter-checkbox');
    const checkAll = document.getElementById('check-all');
    const filterLabel = document.getElementById('filter-label');
    const clearBtn = document.getElementById('clear-filters');

    function applyFilters() {
        const checked = Array.from(checkboxes)
            .filter(c => c.checked && c.value !== 'all')
            .map(c => c.value);
        
        const isAllSelected = checkAll.checked;
        const rows = document.querySelectorAll('.service-row');
        
        // UI Updates
        if (checked.length > 0 && !isAllSelected) {
            filterLabel.innerText = `${checked.length} categorías seleccionadas`;
            clearBtn.classList.remove('d-none');
        } else {
            filterLabel.innerText = 'Todas las categorías';
            clearBtn.classList.add('d-none');
        }

        // Apply display
        rows.forEach(row => {
            const rowCatId = row.dataset.categoryId;
            if (isAllSelected || checked.includes(rowCatId)) {
                row.style.display = '';
                row.style.opacity = '1';
            } else {
                row.style.display = 'none';
            }
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this === checkAll && this.checked) {
                // Si marcamos "Todas", desmarcamos las demás
                checkboxes.forEach(c => { if(c !== checkAll) c.checked = false; });
            } else if (this !== checkAll && this.checked) {
                // Si marcamos una específica, desmarcamos "Todas"
                checkAll.checked = false;
            }
            
            // Si no hay nada marcado, volver a marcar "Todas"
            const anyChecked = Array.from(checkboxes).some(c => c.checked);
            if (!anyChecked) checkAll.checked = true;

            applyFilters();
        });
    });

    function resetFilters() {
        checkboxes.forEach(c => c.checked = (c === checkAll));
        applyFilters();
    }

    // Modal populate logic
    document.querySelectorAll('.edit-category-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = this.dataset;
            document.getElementById('edit-cat-id').value = data.id;
            document.getElementById('edit-cat-name').value = data.name;
            document.getElementById('edit-cat-slug').value = data.slug;
            document.getElementById('edit-cat-icon').value = data.icon;
            document.getElementById('edit-cat-icon-preview').innerText = data.icon;
            document.getElementById('edit-cat-existing-image').value = data.image;
            document.getElementById('edit-cat-description').value = data.description;
            document.getElementById('edit-cat-active').checked = data.active == "1";

            const preview = document.getElementById('edit-cat-preview');
            const previewContainer = document.getElementById('image-preview-container');
            if (data.image) {
                preview.src = window.APP_URL + '/' + data.image;
                previewContainer.classList.remove('d-none');
            } else {
                previewContainer.classList.add('d-none');
            }

            new bootstrap.Modal(document.getElementById('modalEditCategory')).show();
        });
    });
</script>

<style>
    .btn-midnight {
        background: rgba(10, 11, 14, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .btn-midnight:hover {
        background: rgba(10, 11, 14, 0.9);
        border-color: var(--tech-blue);
    }

    .dropdown-item-dark:hover {
        background: rgba(48, 197, 255, 0.1);
        color: var(--tech-blue);
    }

    .form-check-input:checked {
        background-color: var(--tech-blue);
        border-color: var(--tech-blue);
    }

    .border-dashed {
        border-style: dashed !important;
    }

    .bg-white-10 {
        background: rgba(255, 255, 255, 0.1);
    }
</style>
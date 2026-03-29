<div class="row g-4">
    <div class="col-12">
        <a href="<?php echo url('admin/services'); ?>"
            class="text-white-50 text-decoration-none small d-inline-flex align-items-center gap-2 hover-gold mb-3 transition-all">
            <span class="material-symbols-outlined fs-6">arrow_back</span> Volver al catálogo
        </a>

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
        <h2 class="text-white fw-black">Configurar <span class="text-primary">
                <?php echo $service['name']; ?>
            </span></h2>
    </div>

    <!-- General Settings -->
    <div class="col-lg-7">
        <div class="glass-morphism p-4 rounded-5 border-white-10 shadow-2xl">
            <h2 class="text-white h6 fw-black uppercase tracking-widest mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary fs-5">settings</span> Propiedades Generales
            </h2>
            <form action="<?php echo url('admin/services/updateService'); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                <div class="row g-4">
                    <div class="col-md-8">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Nombre del Servicio</label>
                        <input type="text" name="name" class="form-control bg-steel border-white-10 text-white p-3"
                            value="<?php echo $service['name']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Icono Material</label>
                        <div class="d-flex gap-2">
                            <select name="icon" class="form-select bg-steel border-white-10 text-white p-2" required
                                onchange="document.getElementById('service-icon-preview').innerText = this.value">
                                <?php foreach ($curated_icons as $val => $label): ?>
                                    <option value="<?php echo $val; ?>" <?php echo $service['icon'] == $val ? 'selected' : ''; ?>>
                                        <?php echo $val; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="bg-white-5 rounded-3 d-flex align-items-center justify-content-center border border-white-10 shadow-sm"
                                style="width: 50px;">
                                <span class="material-symbols-outlined text-primary"
                                    id="service-icon-preview"><?php echo $service['icon'] ?: 'bolt'; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Categoría</label>
                        <select name="category_id" class="form-select bg-steel border-white-10 text-white p-3">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $service['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Descripción Corta (Landing)</label>
                        <input type="text" name="short_description"
                            class="form-control bg-steel border-white-10 text-white p-3"
                            value="<?php echo $service['short_description']; ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Descripción Completa
                            (Detalle)</label>
                        <textarea name="full_description" class="form-control bg-steel border-white-10 text-white p-3"
                            rows="6" required><?php echo $service['full_description']; ?></textarea>
                    </div>
                    <div class="col-12">
                        <div
                            class="form-check form-switch p-3 bg-white-5 rounded-4 d-flex align-items-center justify-content-between px-4 mt-2">
                            <label class="form-check-label text-white fw-bold mb-0" for="is_active">Activar en la
                                Web</label>
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo $service['is_active'] ? 'checked' : ''; ?>>
                        </div>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit"
                            class="btn btn-primary w-100 py-3 fw-black uppercase tracking-widest shadow-gold">Guardar
                            Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Plans Management -->
    <div class="col-lg-5">
        <h2 class="text-white h6 fw-black uppercase tracking-widest mb-4 px-2">Gestión de Planes de Precios</h2>

        <?php foreach ($plans as $plan): ?>
            <div
                class="glass-morphism p-4 rounded-5 border-white-10 mb-4 bg-white-5 transition-all hover-glow border-opacity-25 <?php echo $plan['is_featured'] ? 'border-primary' : ''; ?>">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex flex-column bg-white-5 rounded-2 overflow-hidden border border-white-10">
                            <a href="<?php echo url('admin/services/reorderPlan/' . $plan['id'] . '/up'); ?>"
                                class="reorder-btn hover-primary d-flex align-items-center justify-content-center"
                                title="Mover arriba">
                                <span class="material-symbols-outlined fs-6">keyboard_arrow_up</span>
                            </a>
                            <a href="<?php echo url('admin/services/reorderPlan/' . $plan['id'] . '/down'); ?>"
                                class="reorder-btn hover-primary d-flex align-items-center justify-content-center border-top border-white-10"
                                title="Mover abajo">
                                <span class="material-symbols-outlined fs-6">keyboard_arrow_down</span>
                            </a>
                        </div>
                        <h6 class="text-white h5 fw-black mb-0">
                            <?php echo $plan['name']; ?>
                        </h6>
                    </div>
                    <span class="text-primary fw-bold">$
                        <?php echo number_format($plan['price'], 0); ?> u$d
                    </span>
                </div>

                <form action="<?php echo url('admin/services/updatePlan'); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                    <div class="mb-4">
                        <label class="text-white-50 x-small uppercase fw-bold mb-2">Features (Uno por línea)</label>
                        <textarea name="features" class="form-control bg-steel border-white-10 text-white x-small" rows="5"
                            placeholder="Feature 1\nFeature 2..."><?php
                            $feats = json_decode($plan['features'], true);
                            echo $feats ? implode("\n", $feats) : '';
                            ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="text-white-50 x-small mb-1 d-block font-size-10 uppercase fw-bold">Nombre
                                Plan</label>
                            <input type="text" name="name"
                                class="form-control form-control-sm bg-steel border-white-10 text-white"
                                value="<?php echo $plan['name']; ?>">
                        </div>
                        <div class="col-6">
                            <label class="text-white-50 x-small mb-1 d-block font-size-10 uppercase fw-bold">Precio
                                ($)</label>
                            <input type="number" name="price"
                                class="form-control form-control-sm bg-steel border-white-10 text-white"
                                value="<?php echo $plan['price']; ?>">
                        </div>
                        <div class="col-6">
                            <div class="form-check small mt-2">
                                <input class="form-check-input" type="checkbox" name="is_featured"
                                    id="feat_<?php echo $plan['id']; ?>" <?php echo $plan['is_featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label text-white-50"
                                    for="feat_<?php echo $plan['id']; ?>">Destacado</label>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <a href="<?php echo url('admin/services/deletePlan/' . $plan['id']); ?>"
                                class="text-danger x-small text-decoration-none me-3"
                                onclick="return confirm('¿Eliminar este plan?')">Eliminar</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill">Actualizar</button>
                        </div>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>

        <div class="glass-morphism p-4 rounded-5 border-white-10 mb-4 bg-white-5 border-dashed"
            style="border-style: dashed !important;">
            <h6 class="text-white-50 x-small uppercase fw-bold mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined fs-6">add_circle</span> Crear Nuevo Plan de Precios
            </h6>
            <form action="<?php echo url('admin/services/storePlan'); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                <div class="row g-2">
                    <div class="col-7">
                        <label class="text-white-50 font-size-10 uppercase fw-bold mb-1">Nombre del Plan</label>
                        <input type="text" name="name"
                            class="form-control form-control-sm bg-steel border-white-10 text-white"
                            placeholder="Ej: Platinum" required>
                    </div>
                    <div class="col-5">
                        <label class="text-white-50 font-size-10 uppercase fw-bold mb-1">Precio ($)</label>
                        <input type="number" name="price"
                            class="form-control form-control-sm bg-steel border-white-10 text-white" placeholder="0"
                            required>
                    </div>
                    <div class="col-12 mt-3">
                        <button type="submit"
                            class="btn btn-primary btn-sm w-100 py-2 fw-bold uppercase tracking-widest shadow-gold">
                            Añadir Plan al Servicio
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .font-size-10 {
        font-size: 10px;
    }

    .hover-glow:hover {
        border-color: rgba(48, 197, 255, 0.4);
        box-shadow: 0 0 20px rgba(48, 197, 255, 0.05);
    }

    .reorder-btn {
        width: 24px;
        height: 20px;
        color: rgba(255, 255, 255, 0.3);
        text-decoration: none;
        transition: all 0.2s;
    }

    .reorder-btn:hover {
        background: rgba(48, 197, 255, 0.2);
        color: var(--tech-blue);
    }
</style>
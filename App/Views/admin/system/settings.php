<div class="row g-4 mb-5" id="systemSettings">
    <div class="col-12 mb-2">
        <h2 class="text-white fw-black mb-1">Configuración del Sistema ⚙️</h2>
        <p class="text-white-50">Gestiona la identidad corporativa, límites operativos y seguridad del CRM.</p>
    </div>

    <div class="col-12">
        <div class="glass-morphism border-white-10 p-4 rounded-4 shadow-lg h-100 overflow-hidden">
            <form action="<?php echo url('admin/system/update'); ?>" method="POST" class="h-100 d-flex flex-column">
                <?php echo csrf_field(); ?>
                
                <!-- Pestañas de Grupo -->
                <ul class="nav nav-pills mb-4 border-white-10 p-2 rounded-pill bg-white-05" id="configTabs" role="tablist" style="width: fit-content;">
                    <?php $i = 0; foreach ($configs as $group => $items): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-4 fw-bold <?php echo $i === 0 ? 'active' : ''; ?>" 
                                    id="tab-<?php echo $group; ?>" 
                                    data-bs-toggle="pill" 
                                    data-bs-target="#group-<?php echo $group; ?>" 
                                    type="button" role="tab">
                                <?php echo strtoupper($group); ?>
                            </button>
                        </li>
                    <?php $i++; endforeach; ?>
                </ul>

                <div class="tab-content flex-grow-1" id="configTabsContent">
                    <?php $i = 0; foreach ($configs as $group => $items): ?>
                        <div class="tab-pane fade <?php echo $i === 0 ? 'show active' : ''; ?>" 
                             id="group-<?php echo $group; ?>" role="tabpanel">
                            
                            <div class="row g-4">
                                <?php foreach ($items as $item): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 bg-white-02 border-white-05 hover-border-gold transition-all h-100">
                                            <label class="form-label text-white-70 small fw-bold mb-1 d-block"><?php echo $item['label']; ?></label>
                                            
                                            <?php if ($item['field_type'] === 'text'): ?>
                                                <input type="text" name="config[<?php echo $item['config_key']; ?>]" 
                                                       value="<?php echo htmlspecialchars($item['config_value']); ?>" 
                                                       class="form-control form-control-sm elegant-input">
                                            
                                            <?php elseif ($item['field_type'] === 'number'): ?>
                                                <input type="number" name="config[<?php echo $item['config_key']; ?>]" 
                                                       value="<?php echo htmlspecialchars($item['config_value']); ?>" 
                                                       class="form-control form-control-sm elegant-input">
                                            
                                            <?php elseif ($item['field_type'] === 'textarea'): ?>
                                                <textarea name="config[<?php echo $item['config_key']; ?>]" 
                                                          class="form-control form-control-sm elegant-input" rows="3"><?php echo htmlspecialchars($item['config_value']); ?></textarea>
                                            
                                            <?php elseif ($item['field_type'] === 'bool'): ?>
                                                <div class="form-check form-switch mt-2">
                                                    <input type="hidden" name="config[<?php echo $item['config_key']; ?>]" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" 
                                                           name="config[<?php echo $item['config_key']; ?>]" value="1" 
                                                           <?php echo $item['config_value'] == '1' ? 'checked' : ''; ?>>
                                                    <span class="text-white-50 small ms-2"><?php echo $item['config_value'] == '1' ? 'Activado' : 'Desactivado'; ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($item['description'])): ?>
                                                <div class="text-white-40 mt-2 x-small">
                                                    <span class="material-symbols-outlined fs-6 align-middle me-1">info</span>
                                                    <?php echo $item['description']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php $i++; endforeach; ?>
                </div>

                <div class="mt-5 pt-4 border-top border-white-10 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-gold d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">save</span>
                        Guardar Cambios Maestros
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .elegant-input {
        background: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-radius: 8px !important;
        padding: 0.6rem 0.8rem !important;
    }

    .elegant-input:focus {
        border-color: #D4AF37 !important;
        box-shadow: 0 0 15px rgba(212, 175, 55, 0.15) !important;
        background: rgba(255, 255, 255, 0.05) !important;
    }

    .nav-pills .nav-link {
        color: rgba(255, 255, 255, 0.6);
        transition: all 0.3s ease;
    }

    .nav-pills .nav-link:hover {
        color: white;
        background: rgba(255,255,255,0.05);
    }

    .nav-pills .nav-link.active {
        background: #D4AF37 !important;
        color: #0A0A0A !important;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
    }
</style>

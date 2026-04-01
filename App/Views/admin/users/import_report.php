<div class="row g-4 mb-5" id="importReport">
    <div class="col-12 mb-2">
        <h2 class="text-white fw-black mb-1">Reporte de Importación Finalizado 📊</h2>
        <p class="text-white-50">Revise los resultados de la operación masiva.</p>
    </div>

    <div class="col-md-4">
        <div class="glass-morphism border-white-10 p-4 rounded-4 shadow-lg text-center h-100">
            <span class="material-symbols-outlined display-1 text-success mb-3">check_circle</span>
            <div class="h2 text-white fw-black">Paso Exitoso</div>
            <p class="text-white-50">Se crearon <span class="text-white fw-bold"><?php echo $success; ?></span> nuevos usuarios en el CRM.</p>
            <div class="mt-4">
                <a href="<?php echo url('admin/users'); ?>" class="btn btn-primary rounded-pill px-4 fw-bold shadow-gold">Ir a Gestión de Usuarios</a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="glass-morphism border-white-10 p-4 rounded-4 shadow-lg h-100 overflow-hidden">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-white-10 pb-3">
                <h5 class="text-white fw-black m-0">Reporte de Errores e Incidencias</h5>
                <span class="badge rounded-pill bg-danger px-3 py-2 fw-bold"><?php echo count($errors); ?> Errores</span>
            </div>

            <div style="max-height: 400px; overflow-y: auto;" class="custom-scroll pe-2">
                <?php if (empty($errors)): ?>
                    <div class="text-center py-5">
                        <span class="material-symbols-outlined display-4 text-white-10 mb-2">sentiment_satisfied</span>
                        <p class="text-white-30">No se detectó ningún problema con los datos subidos.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush bg-transparent">
                        <?php foreach ($errors as $error): ?>
                            <li class="list-group-item bg-transparent border-white-05 text-white-70 p-3 d-flex align-items-center gap-3 hover-bg-light-05 transition-all">
                                <span class="material-symbols-outlined text-danger">cancel</span>
                                <span class="small"><?php echo $error; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="mt-4 pt-3 border-top border-white-10 text-center">
                <p class="text-white-30 x-small italic m-0">"Recomendamos corregir los errores identificados en su archivo original y volver a realizar la importación solo con los registros corregidos."</p>
                <div class="mt-3">
                    <a href="<?php echo url('admin/users/import'); ?>" class="btn btn-outline-light rounded-pill px-4 btn-xs fw-bold border-white-10 hover-gold">
                        Volver a Intentar Importación
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); }
    .custom-scroll::-webkit-scrollbar-thumb { background: var(--vzl-color-gold); border-radius: 10px; }
    .hover-bg-light-05:hover { background: rgba(255,255,255,0.05) !important; cursor: default; }
    .btn-xs { font-size: 0.75rem !important; }
</style>

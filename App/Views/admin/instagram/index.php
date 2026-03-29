<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="h4 mb-1 text-white"><span class="material-symbols-outlined align-middle fs-3 text-accent me-2">auto_awesome</span> Asistente de Estrategia Instagram</h2>
            <p class="text-white-50 mb-0 small">Planificación semanal inteligente impulsada por IA (Contextual Strategy).</p>
        </div>
        <a href="<?php echo url('admin/instagram/generate'); ?>" class="btn btn-primary shadow-gold text-midnight fw-bold d-flex align-items-center gap-2">
            <span class="material-symbols-outlined">rocket_launch</span> Generar Nueva Semana
        </a>
    </div>
</div>

<div class="card glass-morphism border-white-10">
    <div class="card-header border-bottom border-white-10 bg-transparent p-4">
        <h3 class="h6 mb-0 text-white uppercase tracking-widest">Calendarios Generados</h3>
    </div>
    <div class="card-body p-0">
        <?php if (empty($calendars)): ?>
            <div class="text-center p-5">
                <span class="material-symbols-outlined display-1 text-white-10 mb-3">calendar_month</span>
                <p class="text-white-50 mb-4">No has generado ninguna estrategia todavía.</p>
                <a href="<?php echo url('admin/instagram/generate'); ?>" class="btn btn-outline-primary shadow-gold">Comenzar ahora</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 bg-transparent">
                    <thead>
                        <tr>
                            <th class="border-white-10 text-white-50 small uppercase tracking-widest ps-4">Semana</th>
                            <th class="border-white-10 text-white-50 small uppercase tracking-widest">Fecha Inicio</th>
                            <th class="border-white-10 text-white-50 small uppercase tracking-widest">Estado</th>
                            <th class="border-white-10 text-white-50 small uppercase tracking-widest">Creado en</th>
                            <th class="border-white-10 text-white-50 small uppercase tracking-widest text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($calendars as $cal): ?>
                            <tr>
                                <td class="border-white-10 align-middle ps-4">
                                    <span class="fw-bold text-accent"><?php echo htmlspecialchars($cal['week_label']); ?></span>
                                </td>
                                <td class="border-white-10 align-middle text-white-50">
                                    <?php echo date('d/m/Y', strtotime($cal['start_date'])); ?>
                                </td>
                                <td class="border-white-10 align-middle">
                                    <?php if ($cal['status'] === 'finalized'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Finalizado</span>
                                    <?php else: ?>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">Borrador</span>
                                    <?php endif; ?>
                                </td>
                                <td class="border-white-10 align-middle text-white-50 small">
                                    <?php echo date('d/m/Y H:i', strtotime($cal['created_at'])); ?>
                                </td>
                                <td class="border-white-10 align-middle text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?php echo url('admin/instagram/view?id=' . $cal['id']); ?>" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" title="Ver Detalle">
                                            <span class="material-symbols-outlined fs-6">visibility</span>
                                        </a>
                                        <?php if ($cal['status'] === 'finalized'): ?>
                                            <a href="<?php echo url('admin/instagram/downloadCsv?id=' . $cal['id']); ?>" class="btn btn-sm btn-outline-success d-flex align-items-center gap-1" title="Descargar CSV">
                                                <span class="material-symbols-outlined fs-6">download</span>
                                            </a>
                                        <?php endif; ?>
                                        <form action="<?php echo url('admin/instagram/delete'); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este calendario?');">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo $cal['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" title="Eliminar">
                                                <span class="material-symbols-outlined fs-6">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

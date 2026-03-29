<?php
/**
 * Vista Workspace del Cliente — SPRINT 2.2
 * Agrega botones de aprobación/rechazo por entregable
 */
?>
<div class="row g-4 mb-5">
    <div class="col-12">
        <h2 class="text-white fw-black mb-1">Centro de Entregables 📂</h2>
        <p class="text-white-50">Accede a todos los resultados de tus proyectos de ingeniería de datos.</p>
    </div>

    <?php if (empty($services)): ?>
        <div class="col-12">
            <div class="glass-morphism p-5 text-center rounded-5">
                <span class="material-symbols-outlined display-1 text-white-10 mb-3">folder_open</span>
                <h4 class="text-white fw-bold">No hay servicios activos aún</h4>
                <p class="text-white-50">Tus entregables aparecerán aquí una vez que tus servicios sean activados.</p>
                <a href="<?php echo url('dashboard'); ?>" class="btn btn-primary rounded-pill px-4 mt-2">Ir al Dashboard</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($services as $service): ?>
            <div class="col-12">
                <div class="glass-morphism rounded-5 border-white-10 overflow-hidden mb-4">
                    <div class="p-4 bg-white-5 d-flex align-items-start justify-content-between border-bottom border-white-10 flex-wrap gap-3">
                        <div>
                            <span class="badge bg-gold text-black x-small fw-black uppercase tracking-widest mb-2">
                                <?php echo $service['plan_name']; ?>
                            </span>
                            <h4 class="text-white fw-bold mb-0">
                                <?php echo $service['name']; ?>
                            </h4>
                        </div>
                        <div class="text-end">
                            <div class="d-flex align-items-center gap-3 mb-1">
                                <div class="text-end">
                                    <span class="text-white-50 x-small d-block">Progreso del Proyecto</span>
                                    <span class="text-white small fw-bold"><?php echo $service['progress_percent']; ?>%</span>
                                </div>
                                <div class="progress bg-white-5" style="width: 100px; height: 8px;">
                                    <div class="progress-bar bg-accent" role="progressbar"
                                        style="width: <?php echo $service['progress_percent']; ?>%"
                                        aria-valuenow="<?php echo $service['progress_percent']; ?>" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                            <span class="text-white-50 x-small d-block mt-2">Activado el:
                                <?php echo date('d M, Y', strtotime($service['start_date'])); ?></span>
                        </div>
                    </div>

                    <?php
                    $invoiceTotal = (float) $service['invoice_total'];
                    $invoicePaid = (float) $service['invoice_paid'];
                    $invoicePending = (float) $service['invoice_pending'];
                    $payPercent = $invoiceTotal > 0 ? round(($invoicePaid / $invoiceTotal) * 100) : 100;
                    $invoiceStatus = $service['invoice_status'];
                    ?>
                    <?php if ($invoiceStatus !== 'paid'): ?>
                        <div class="px-4 pt-3 pb-0">
                            <div class="rounded-4 p-3 border border-white-10 bg-white-5">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="x-small uppercase tracking-widest text-white-50 fw-bold d-flex align-items-center gap-1">
                                        <span class="material-symbols-outlined fs-6">payments</span>Estado de Pago
                                    </span>
                                    <a href="<?php echo url('invoice/show/' . $service['invoice_id_ref']); ?>"
                                        class="x-small text-primary text-decoration-none hover-gold transition-all">
                                        Ver factura <span class="material-symbols-outlined fs-6 align-middle">arrow_forward</span>
                                    </a>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="x-small text-white-50">Pagado: <span class="text-success fw-bold">$<?php echo number_format($invoicePaid, 2); ?></span></span>
                                    <span class="x-small text-white-50">Pendiente: <span class="text-warning fw-bold">$<?php echo number_format($invoicePending, 2); ?></span></span>
                                </div>
                                <div class="progress bg-white-5 rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: <?php echo $payPercent; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="px-4 pt-3 pb-0">
                            <div class="rounded-4 p-2 px-3 border border-success border-opacity-25 bg-success bg-opacity-10 d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined text-success fs-5">check_circle</span>
                                <span class="x-small text-success fw-bold">Pago Completo — $<?php echo number_format($invoiceTotal, 2); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="p-4">
                        <div class="row g-3">
                            <?php if (isset($deliverables[$service['id']]) && !empty($deliverables[$service['id']])): ?>
                                <?php foreach ($deliverables[$service['id']] as $file): ?>
                                    <?php
                                    $statusConfig = [
                                        'pending_review' => ['label' => 'Pendiente de Revisión', 'icon' => 'pending', 'class' => 'bg-warning-subtle text-warning border-warning'],
                                        'approved'       => ['label' => 'Aprobado', 'icon' => 'check_circle', 'class' => 'bg-success-subtle text-success border-success'],
                                        'rejected'       => ['label' => 'Rechazado', 'icon' => 'cancel', 'class' => 'bg-danger-subtle text-danger border-danger'],
                                    ];
                                    $st = $statusConfig[$file['status'] ?? 'pending_review'] ?? $statusConfig['pending_review'];
                                    ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="p-3 rounded-4 bg-steel border border-white-5 h-100 hover-lift transition-all position-relative">
                                            <!-- Status badge -->
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <span class="badge border <?php echo $st['class']; ?> x-small fw-bold d-flex align-items-center gap-1 px-2 py-1">
                                                    <span class="material-symbols-outlined" style="font-size:13px"><?php echo $st['icon']; ?></span>
                                                    <?php echo $st['label']; ?>
                                                </span>
                                            </div>

                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                <div class="rounded-3 bg-white-5 p-2 d-flex align-items-center justify-content-center text-accent"
                                                    style="width: 48px; height: 48px; flex-shrink:0">
                                                    <span class="material-symbols-outlined fs-2">
                                                        <?php
                                                        switch ($file['file_type']) {
                                                            case 'document': echo 'description'; break;
                                                            case 'code':     echo 'terminal';    break;
                                                            case 'data':     echo 'database';    break;
                                                            case 'image':    echo 'image';       break;
                                                            default:         echo 'draft';
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <h6 class="text-white fw-bold mb-0 text-truncate"><?php echo htmlspecialchars($file['title']); ?></h6>
                                                    <span class="text-white-50 x-small">v<?php echo $file['version']; ?> | <?php echo number_format($file['file_size'] / 1024, 1); ?> KB</span>
                                                </div>
                                            </div>

                                            <p class="text-white-50 x-small mb-3 line-clamp-2"><?php echo htmlspecialchars($file['description']); ?></p>

                                            <?php if (!empty($file['review_notes']) && $file['status'] === 'rejected'): ?>
                                                <div class="rounded-3 p-2 mb-3 bg-danger bg-opacity-10 border border-danger border-opacity-25">
                                                    <p class="x-small text-danger mb-0">
                                                        <span class="material-symbols-outlined" style="font-size:13px;vertical-align:middle">info</span>
                                                        <?php echo htmlspecialchars($file['review_notes']); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Download button -->
                                            <a href="<?php echo url('project/download/' . $file['id']); ?>"
                                                class="btn btn-outline-light btn-sm w-100 rounded-pill border-white-10 fw-bold mb-2">
                                                <span class="material-symbols-outlined fs-6 align-middle me-1">download</span> Descargar
                                            </a>

                                            <!-- Approve / Reject buttons (only if pending) -->
                                            <?php if (($file['status'] ?? 'pending_review') === 'pending_review'): ?>
                                                <div class="d-flex gap-2 mt-1">
                                                    <button type="button" class="btn btn-sm flex-grow-1 rounded-pill fw-bold btn-review-approve"
                                                        data-id="<?php echo $file['id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($file['title']); ?>"
                                                        style="background: rgba(76,175,80,0.15); color: #4CAF50; border: 1px solid rgba(76,175,80,0.3);">
                                                        <span class="material-symbols-outlined fs-6 align-middle">thumb_up</span> Aprobar
                                                    </button>
                                                    <button type="button" class="btn btn-sm flex-grow-1 rounded-pill fw-bold btn-review-reject"
                                                        data-id="<?php echo $file['id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($file['title']); ?>"
                                                        style="background: rgba(255,85,85,0.12); color: #FF5555; border: 1px solid rgba(255,85,85,0.3);">
                                                        <span class="material-symbols-outlined fs-6 align-middle">thumb_down</span> Rechazar
                                                    </button>
                                                </div>
                                            <?php elseif ($file['status'] === 'approved'): ?>
                                                <p class="text-center x-small text-success mb-0 mt-1">
                                                    <span class="material-symbols-outlined" style="font-size:13px;vertical-align:middle">verified</span>
                                                    Aprobado el <?php echo date('d/m/Y', strtotime($file['reviewed_at'])); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 py-4 text-center">
                                    <p class="text-white-50 italic x-small mb-0">El equipo técnico aún está procesando los entregables finales para este servicio.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal: Review Deliverable -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-midnight border border-white-10 rounded-5">
            <form id="reviewForm" method="POST" action="">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" id="review_action" value="">
                <div class="modal-header border-bottom border-white-10 py-3 px-4">
                    <h5 class="modal-title text-white fw-bold" id="reviewModalTitle">Revisar Entregable</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-white-50 small mb-3" id="reviewModalDesc"></p>
                    <div id="notesWrapper">
                        <label class="form-label text-white-50 x-small fw-bold">Notas adicionales <span class="text-white-25">(opcional para aprobación, recomendado para rechazo)</span></label>
                        <textarea name="review_notes" id="review_notes_input" class="form-control bg-steel border-white-10 text-white" rows="3"
                            placeholder="Describe qué necesita ajuste o tu confirmación..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-white-10 px-4 py-3 d-flex gap-2">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4 border-white-10" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4 fw-bold" id="reviewSubmitBtn">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal     = new bootstrap.Modal(document.getElementById('reviewModal'));
    const form      = document.getElementById('reviewForm');
    const actionIn  = document.getElementById('review_action');
    const titleEl   = document.getElementById('reviewModalTitle');
    const descEl    = document.getElementById('reviewModalDesc');
    const submitBtn = document.getElementById('reviewSubmitBtn');

    document.querySelectorAll('.btn-review-approve').forEach(btn => {
        btn.addEventListener('click', () => {
            const id    = btn.dataset.id;
            const title = btn.dataset.title;
            form.action       = `<?php echo url('project/review/'); ?>${id}`;
            actionIn.value    = 'approve';
            titleEl.textContent = `Aprobar: ${title}`;
            descEl.textContent  = '¿Confirmas que este entregable cumple con lo acordado?';
            submitBtn.className = 'btn rounded-pill px-4 fw-bold btn-success';
            submitBtn.textContent = '✅ Aprobar';
            modal.show();
        });
    });

    document.querySelectorAll('.btn-review-reject').forEach(btn => {
        btn.addEventListener('click', () => {
            const id    = btn.dataset.id;
            const title = btn.dataset.title;
            form.action       = `<?php echo url('project/review/'); ?>${id}`;
            actionIn.value    = 'reject';
            titleEl.textContent = `Rechazar: ${title}`;
            descEl.textContent  = 'Por favor, indica los motivos o correcciones necesarias (las notas llegarán al equipo).';
            submitBtn.className = 'btn rounded-pill px-4 fw-bold btn-danger';
            submitBtn.textContent = '❌ Confirmar Rechazo';
            modal.show();
        });
    });
});
</script>
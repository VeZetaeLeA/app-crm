<div class="row g-4 mb-5">
    <div class="col-12">
        <h2 class="text-white fw-black mb-1">Gestión de Workspaces 🔧</h2>
        <p class="text-white-50">Gestiona los entregables técnicos para cada servicio activo.</p>
    </div>

    <div class="row g-4">
        <?php foreach ($services as $service): ?>
            <div class="col-md-6 col-xxl-4">
                <div class="glass-morphism h-100 rounded-5 border-white-10 overflow-hidden hover-lift transition-all">
                    <div
                        class="p-4 bg-white-5 border-bottom border-white-10 d-flex align-items-center justify-content-between">
                        <div class="rounded-circle bg-white-5 d-flex align-items-center justify-content-center text-accent fw-bold"
                            style="width: 40px; height: 40px;">
                            <?php echo strtoupper(substr($service['client_name'], 0, 1)); ?>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle x-small px-3">
                            <?php echo strtoupper($service['status']); ?>
                        </span>
                    </div>
                    <div class="p-4">
                        <h5 class="text-white fw-bold mb-1"><?php echo $service['name']; ?></h5>
                        <p class="text-white-50 x-small mb-3"><?php echo $service['plan_name']; ?></p>

                        <div class="bg-deep-black bg-opacity-30 rounded-4 p-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50 x-small">Cliente:</span>
                                <span class="text-white small fw-bold"><?php echo $service['client_name']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-white-50 x-small">Iniciado:</span>
                                <span
                                    class="text-white small"><?php echo date('d/m/Y', strtotime($service['start_date'])); ?></span>
                            </div>
                        </div>

                        <?php
                        $invTotal = (float) $service['invoice_total'];
                        $invPaid = (float) $service['invoice_paid'];
                        $invPending = max(0, (float) $service['invoice_pending']);
                        $payPct = $invTotal > 0 ? round(($invPaid / $invTotal) * 100) : 100;
                        $invStatus = $service['invoice_status'];
                        $barClass = $invStatus === 'paid' ? 'bg-success' : ($invPending > 0 ? 'bg-warning' : 'bg-success');
                        ?>
                        <div class="rounded-4 p-3 border border-white-10 bg-white-5 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="x-small uppercase tracking-widest text-white-50 fw-bold">
                                    Facturación
                                </span>
                                <?php if ($invStatus === 'paid'): ?>
                                    <span
                                        class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 x-small px-2">Pagado</span>
                                <?php elseif ($invStatus === 'partial'): ?>
                                    <span
                                        class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 x-small px-2">Pago
                                        Parcial</span>
                                <?php else: ?>
                                    <span
                                        class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25 x-small px-2">Pendiente</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="x-small text-white-50">Abonado:</span>
                                <span class="x-small text-success fw-bold">$<?php echo number_format($invPaid, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="x-small text-white-50">Pendiente:</span>
                                <span
                                    class="x-small fw-bold <?php echo $invPending > 0 ? 'text-warning' : 'text-success'; ?>">
                                    $<?php echo number_format($invPending, 2); ?>
                                </span>
                            </div>
                            <div class="progress rounded-pill bg-white-5 mb-1" style="height: 5px;">
                                <div class="progress-bar <?php echo $barClass; ?>" style="width: <?php echo $payPct; ?>%;">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="x-small text-white-50">Total:
                                    $<?php echo number_format($invTotal, 2); ?></span>
                                <a href="<?php echo url('invoice/show/' . $service['invoice_id_ref']); ?>"
                                    class="x-small text-primary text-decoration-none hover-gold transition-all">
                                    Ver factura →
                                </a>
                            </div>
                        </div>

                        <a href="<?php echo url('project/manage/' . $service['id']); ?>"
                            class="btn btn-primary w-100 rounded-pill fw-bold">
                            <span class="material-symbols-outlined fs-6 align-middle me-1">folder_shared</span> Gestionar
                            Workspace
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .hover-lift:hover {
        transform: translateY(-5px);
        border-color: rgba(212, 175, 55, 0.4) !important;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
    }
</style>
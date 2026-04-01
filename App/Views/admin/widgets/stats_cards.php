<div class="row g-4">
    <!-- Skeleton Loaders -->
    <?php for ($i = 0; $i < 4; $i++): ?>
        <div class="col-6 col-md-3 skeleton-loader no-print">
            <div class="glass-morphism p-4 rounded-4 border-white-10 h-100">
                <div class="skeleton skeleton-text mb-3" style="width: 50%"></div>
                <div class="d-flex justify-content-between align-items-end">
                    <div class="skeleton skeleton-title mb-0" style="width: 40%"></div>
                    <div class="skeleton skeleton-text mb-0" style="width: 20%"></div>
                </div>
            </div>
        </div>
    <?php endfor; ?>

    <div class="col-6 col-md-3">
        <div class="vzl-card-glass component-kpi p-4 h-100 position-relative border-white-15">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <span class="material-symbols-outlined fs-1 text-primary">confirmation_number</span>
            </div>
            <p class="text-white-50 x-small fw-bold uppercase tracking-widest mb-1"><?= __('kpi.total_tickets') ?></p>
            <div class="d-flex align-items-end justify-content-between mt-3">
                <h3 class="vzl-metric-value mb-0">
                    <?php echo $stats['total_tickets']; ?>
                </h3>
                <span class="vzl-badge vzl-badge-neutral"><?php echo $stats['closed_tickets_pct']; ?>% <?= __('kpi.closed') ?></span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vzl-card-glass component-kpi p-4 h-100 position-relative border-white-15">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <span class="material-symbols-outlined fs-1 text-warning">pending</span>
            </div>
            <p class="text-white-50 x-small fw-bold uppercase tracking-widest mb-1"><?= __('kpi.pending') ?></p>
            <div class="d-flex align-items-end justify-content-between mt-3">
                <h3 class="vzl-metric-value mb-0">
                    <?php echo $stats['open_tickets']; ?>
                </h3>
                <span class="vzl-badge vzl-badge-warning"><?= __('kpi.critical') ?></span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vzl-card-glass component-kpi p-4 h-100 position-relative border-white-15">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <span class="material-symbols-outlined fs-1 text-success">hub</span>
            </div>
            <p class="text-white-50 x-small fw-bold uppercase tracking-widest mb-1"><?= __('kpi.active_services') ?></p>
            <div class="d-flex align-items-end justify-content-between mt-3">
                <h3 class="vzl-metric-value mb-0">
                    <?php echo $stats['active_services']; ?>
                </h3>
                <span class="vzl-badge vzl-badge-success"><?php echo $stats['paid_invoices_pct']; ?>% <?= __('kpi.paid') ?></span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vzl-card-glass component-kpi p-4 h-100 position-relative border-white-15">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <span class="material-symbols-outlined fs-1 text-primary">person_add</span>
            </div>
            <p class="text-white-50 x-small fw-bold uppercase tracking-widest mb-1"><?= __('kpi.total_users') ?></p>
            <div class="d-flex align-items-end justify-content-between mt-3">
                <h3 class="vzl-metric-value mb-0">
                    <?php echo $stats['total_users']; ?>
                </h3>
                <div class="d-flex gap-2 text-white-50 x-small fw-bold font-mono">
                    <span title="<?= __('kpi.clients') ?>">C:<?php echo $stats['users_breakdown']['client'] ?? 0; ?></span>
                    <span title="<?= __('kpi.staff') ?>">S:<?php echo $stats['users_breakdown']['staff'] ?? 0; ?></span>
                    <span class="text-primary" title="<?= __('kpi.admin') ?>">A:<?php echo ($stats['users_breakdown']['admin'] ?? 0) + ($stats['users_breakdown']['super_admin'] ?? 0); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-4">
    <div class="col-12 d-flex align-items-center justify-content-between mb-2">
        <div>
            <h2 class="text-white fw-black mb-1"><?= __('tickets.management_center') ?></h2>
            <p class="text-white-50"><?= __('tickets.management_subtitle') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo url('ticket/exportCsv'); ?>"
                class="btn btn-outline-white btn-sm px-4 rounded-pill border-white-10 text-white-50 d-flex align-items-center gap-2 no-print">
                <span class="material-symbols-outlined fs-6">download</span>
                <?= __('tickets.download_csv') ?>
            </a>
            <a href="<?php echo url('ticket/request'); ?>"
                class="btn btn-primary btn-sm px-4 fw-bold rounded-pill shadow-gold"><?= __('tickets.new_ticket') ?></a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="col-12">
        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <div class="glass-morphism-premium p-3 rounded-4 h-100">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1"><?= __('kpi.total_tickets') ?></p>
                    <h3 class="text-white mb-0">
                        <?php echo count($tickets); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-morphism-premium p-3 rounded-4 h-100">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1"><?= __('kpi.pending') ?></p>
                    <h3 class="text-warning mb-0">
                        <?php echo count(array_filter($tickets, fn($t) => $t['status'] == 'open')); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-morphism-premium p-3 rounded-4 h-100">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1"><?= __('status.in_progress') ?></p>
                    <h3 class="text-info mb-0">
                        <?php echo count(array_filter($tickets, fn($t) => $t['status'] == 'in_progress')); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-morphism-premium p-3 rounded-4 h-100">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1"><?= __('kpi.closed') ?></p>
                    <h3 class="text-success mb-0">
                        <?php echo count(array_filter($tickets, fn($t) => $t['status'] == 'closed')); ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="col-12">
        <div class="glass-morphism-premium rounded-5 overflow-hidden shadow-2xl">
            <div class="p-4 border-bottom border-white-10 bg-white-5 d-flex align-items-center justify-content-between">
                <h5 class="text-white h6 mb-0 fw-bold uppercase tracking-widest"><?= __('tickets.requests_list') ?></h5>
                <div class="input-group input-group-sm w-auto">
                    <span class="input-group-text bg-steel border-white-10 text-white-50">
                        <span class="material-symbols-outlined fs-6">filter_list</span>
                    </span>
                    <select class="form-select bg-steel border-white-10 text-white x-small fw-bold uppercase">
                        <option value=""><?= __('tickets.all_statuses') ?></option>
                        <option value="open"><?= __('status.open') ?>s</option>
                        <option value="in_progress"><?= __('status.in_progress') ?></option>
                        <option value="resolved"><?= __('status.resolved') ?>s</option>
                        <option value="closed"><?= __('status.closed') ?>s</option>
                        <option value="void"><?= __('status.void') ?>s</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-deep-black">
                        <tr class="x-small uppercase text-white-50 tracking-widest">
                            <th class="p-4 border-0 text-start"><?= __('common.actions') ?></th>
                            <th class="p-4 border-0"><?= __('tickets.ticket_number') ?></th>
                            <th class="p-4 border-0"><?= __('tickets.client') ?></th>
                            <th class="p-4 border-0"><?= __('tickets.service_plan') ?></th>
                            <th class="p-4 border-0"><?= __('common.status') ?></th>
                            <th class="p-4 border-0 text-center">SLA</th>
                            <th class="p-4 border-0"><?= __('common.date') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td class="p-4 text-start">
                                    <a href="<?php echo url('ticket/detail/' . $t['id']); ?>"
                                        class="btn btn-outline-white btn-sm rounded-pill px-3 border-white-10 x-small uppercase fw-bold">
                                        <?= __('tickets.view_detail') ?>
                                    </a>
                                </td>
                                <td class="p-4">
                                    <span class="fw-black text-primary font-monospace">
                                        <?php echo $t['ticket_number']; ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-white small fw-bold">
                                            <?php echo $t['client_name']; ?>
                                        </span>
                                        <?php
                                        $score = $t['lead_score'] ?? 0;
                                        $scoreClass = $score >= 75 ? 'bg-gold text-deep-black' : ($score >= 40 ? 'bg-primary' : 'bg-white-10 text-white-50');
                                        ?>
                                        <span
                                            class="badge <?php echo $scoreClass; ?> rounded-pill x-small px-2 py-1 fw-bold"
                                            style="font-size: 0.65rem;" title="Lead Intelligence Score">
                                            <?php echo $score; ?> <?= __('tickets.points') ?>
                                            <?php if ($score >= 75): ?> 🔥<?php endif; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="small text-white">
                                        <?php echo $t['service_name']; ?>
                                    </div>
                                    <div class="x-small text-white-50">
                                        <?php echo $t['plan_name']; ?>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <?php
                                    $statusClass = [
                                        'open' => 'bg-danger-subtle',
                                        'in_analysis' => 'bg-warning-subtle',
                                        'budget_sent' => 'bg-info-subtle',
                                        'budget_approved' => 'bg-success-subtle',
                                        'budget_rejected' => 'bg-danger-subtle',
                                        'invoiced' => 'bg-info-subtle',
                                        'payment_pending' => 'bg-warning-subtle',
                                        'active' => 'bg-success-subtle',
                                        'resolved' => 'bg-success-subtle',
                                        'closed' => 'bg-secondary-subtle',
                                        'void' => 'bg-dark text-white'
                                    ];
                                    $cls = $statusClass[$t['status']] ?? 'bg-white-10';
                                    ?>
                                    <span
                                        class="badge <?php echo $cls; ?> x-small uppercase fw-bold tracking-tighter px-2 py-1">
                                        <?php echo translateStatus($t['status']); ?>
                                    </span>
                                    <?php if (isset($t['is_at_risk']) && $t['is_at_risk']): ?>
                                        <span class="badge border border-warning text-warning x-small fw-bold px-2 py-1 ms-2"
                                            title="<?php echo htmlspecialchars($t['risk_reason']); ?>">
                                            <span class="material-symbols-outlined fs-6 align-middle me-1"
                                                style="font-size: 14px !important;">warning</span>
                                            <?= __('tickets.sla_at_risk_label') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <?php if (!empty($t['sla_deadline']) && !in_array($t['status'], ['closed', 'void', 'resolved'])): 
                                        $deadline = strtotime($t['sla_deadline']);
                                        $now = time();
                                        $diff = $deadline - $now;
                                        $diffHours = $diff / 3600;

                                         if ($diff < 0) {
                                            $slaCls = 'bg-danger';
                                            $slaLabel = __('tickets.sla_overdue');
                                        } elseif ($diffHours < 12) {
                                            $slaCls = 'bg-warning text-dark';
                                            $slaLabel = __('tickets.sla_at_risk');
                                        } else {
                                            $slaCls = 'bg-success';
                                            $slaLabel = __('tickets.sla_on_time');
                                        }
                                    ?>
                                        <span class="badge <?php echo $slaCls; ?> rounded-pill x-small fw-bold px-2 py-1"
                                              title="Vence: <?php echo date('d/m/Y H:i', $deadline); ?>">
                                            <?php echo $slaLabel; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-white-10 material-symbols-outlined fs-6" style="opacity: 0.2;">verified</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <span class="text-white-50 x-small">
                                        <?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="7" class="p-5 text-center text-white-50 italic">
                                    <?= __('tickets.no_tickets_found') ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-white-5 border-top border-white-10 text-center">
                <span class="text-white-50 x-small uppercase tracking-widest">
                    <?= __('tickets.showing') ?>
                    <?php echo count($tickets); ?> <?= __('tickets.filtered_records') ?>
                </span>
            </div>
        </div>
    </div>
</div>
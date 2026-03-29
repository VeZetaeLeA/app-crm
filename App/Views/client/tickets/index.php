<div class="row g-4">
    <div class="col-12 d-flex align-items-center justify-content-between mb-2">
        <div>
            <h2 class="text-white fw-black mb-1"><?= __('tickets.my_requests') ?></h2>
            <p class="text-white-50"><?= __('tickets.my_requests_subtitle') ?></p>
        </div>
        <a href="<?php echo url('ticket/request'); ?>"
            class="btn btn-primary btn-sm px-4 fw-bold rounded-pill shadow-gold"><?= __('tickets.new_request_btn') ?></a>
    </div>

    <!-- Tickets Table -->
    <div class="col-12">
        <div class="glass-morphism rounded-5 border-white-10 overflow-hidden shadow-2xl">
            <div class="p-4 border-bottom border-white-10 bg-white-5 d-flex align-items-center justify-content-between">
                <h5 class="text-white h6 mb-0 fw-bold uppercase tracking-widest"><?= __('tickets.history') ?></h5>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-deep-black">
                        <tr class="x-small uppercase text-white-50 tracking-widest">
                            <th class="p-4 border-0 text-start"><?= __('common.actions') ?></th>
                            <th class="p-4 border-0"><?= __('tickets.ticket_number') ?></th>
                            <th class="p-4 border-0"><?= __('tickets.subject') ?></th>
                            <th class="p-4 border-0"><?= __('common.search') == 'Search' ? 'Plan' : 'Plan' ?></th>
                            <th class="p-4 border-0"><?= __('common.status') ?></th>
                            <th class="p-4 border-0"><?= __('common.date') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td class="p-4 text-start">
                                    <a href="<?php echo url('ticket/detail/' . $t['id']); ?>"
                                        class="btn btn-outline-white btn-sm rounded-pill px-3 border-white-10 x-small uppercase fw-bold">
                                        <?= __('tickets.view_details') ?>
                                    </a>
                                </td>
                                <td class="p-4">
                                    <span class="fw-black text-primary font-monospace">
                                        <?php echo $t['ticket_number']; ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="text-white small fw-bold">
                                        <?php echo $t['subject']; ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="x-small text-white-50 uppercase">
                                        <?php echo $t['plan_name']; ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <?php
                                    $statusClassClient = [
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
                                    $cls = $statusClassClient[$t['status']] ?? 'bg-white-10';
                                    ?>
                                    <span
                                        class="badge <?php echo $cls; ?> x-small uppercase fw-bold tracking-tighter px-2 py-1">
                                        <?php echo translateStatus($t['status']); ?>
                                    </span>
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
                                <td colspan="6" class="p-5 text-center text-white-50 italic">
                                    <?= __('tickets.no_tickets_yet') ?> <a href="<?php echo url('ticket/request'); ?>"
                                        class="text-primary decoration-none"><?= __('tickets.create_first') ?></a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-white-5 border-top border-white-10 text-center">
                <span class="text-white-50 x-small uppercase tracking-widest">
                    <?= __('tickets.total') ?>
                    <?php echo count($tickets); ?> <?= __('common.search') == 'Search' ? 'tickets' : 'tickets' ?>
                </span>
            </div>
        </div>
    </div>
</div>
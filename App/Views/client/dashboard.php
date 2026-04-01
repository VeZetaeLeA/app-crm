<div class="row g-4 mb-4 align-items-center">
    <!-- Welcome Header -->
    <div class="col-lg-5">
        <h2 class="text-white fw-black mb-1"><?= __('dashboard.client_welcome', ['name' => explode(' ', \Core\Auth::user()['name'])[0]]) ?></h2>
        <p class="text-white-50 mb-0"><?= __('dashboard.client_subtitle') ?></p>
    </div>

    <!-- Roadmap / Próximos pasos -->
    <div class="col-lg-7">
        <div class="glass-morphism-premium p-3 rounded-4 overflow-auto">
            <?php
            // Determine status of the latest ticket for the roadmap
            $latest_ticket_status = !empty($tickets) ? $tickets[0]['status'] : 'none';

            // Define flags for each step
            $step_registro_done = in_array($latest_ticket_status, ['open', 'in_analysis', 'budget_sent', 'budget_approved', 'budget_rejected', 'invoiced', 'payment_pending', 'active', 'closed']);
            $step_registro_active = $latest_ticket_status == 'open';

            $step_analisis_done = in_array($latest_ticket_status, ['in_analysis', 'budget_sent', 'budget_approved', 'budget_rejected', 'invoiced', 'payment_pending', 'active', 'closed']);
            $step_analisis_active = $latest_ticket_status == 'in_analysis';

            $step_propuesta_done = in_array($latest_ticket_status, ['budget_sent', 'budget_approved', 'budget_rejected', 'invoiced', 'payment_pending', 'active', 'closed']);
            $step_propuesta_active = $latest_ticket_status == 'budget_sent';
            ?>
            <div class="d-flex align-items-center gap-3 w-100">
                <span class="text-white-50 uppercase tracking-widest x-small fw-bold flex-shrink-0"><?= __('dashboard.next_steps') ?></span>

                <div class="d-flex align-items-center flex-grow-1">
                    <!-- Registro -->
                    <div
                        class="d-flex flex-column align-items-center position-relative z-1 <?php echo !$step_registro_done && !$step_registro_active ? 'opacity-50' : ''; ?>">
                        <div class="rounded-circle <?php echo $step_registro_active ? 'bg-primary text-white shadow-gold border-0' : ($step_registro_done ? 'bg-success text-white shadow-gold border-0' : 'bg-midnight border border-white-10 text-white-50'); ?> d-flex align-items-center justify-content-center p-1 mb-1"
                            style="width: 28px; height: 28px;">
                            <span
                                class="material-symbols-outlined x-small"><?php echo $step_registro_done && !$step_registro_active ? 'check' : 'edit_document'; ?></span>
                        </div>
                        <span class="text-white x-small fw-bold"><?= __('dashboard.step_registration') ?></span>
                    </div>

                    <div class="flex-grow-1 bg-white-10" style="height: 2px; margin: 0 5px; transform: translateY(-10px);">
                        <?php if ($step_analisis_done || $step_analisis_active): ?>
                            <div class="bg-success h-100" style="width: 100%;"></div><?php endif; ?>
                    </div>

                    <!-- Análisis -->
                    <div
                        class="d-flex flex-column align-items-center position-relative z-1 <?php echo (!$step_analisis_done && !$step_analisis_active) ? 'opacity-50' : ''; ?>">
                        <div class="rounded-circle <?php echo $step_analisis_active ? 'bg-primary text-white shadow-gold border-0' : ($step_analisis_done ? 'bg-success text-white shadow-gold border-0' : 'bg-midnight border border-white-10 text-white-50'); ?> d-flex align-items-center justify-content-center p-1 mb-1"
                            style="width: 28px; height: 28px;">
                            <span
                                class="material-symbols-outlined x-small"><?php echo $step_analisis_done && !$step_analisis_active ? 'check' : 'assignment'; ?></span>
                        </div>
                        <span class="text-white x-small fw-bold"><?= __('dashboard.step_analysis') ?></span>
                    </div>

                    <div class="flex-grow-1 bg-white-10" style="height: 2px; margin: 0 5px; transform: translateY(-10px);">
                        <?php if ($step_propuesta_done || $step_propuesta_active): ?>
                            <div class="bg-success h-100" style="width: 100%;"></div><?php endif; ?>
                    </div>

                    <!-- Propuesta -->
                    <div
                        class="d-flex flex-column align-items-center position-relative z-1 <?php echo (!$step_propuesta_done && !$step_propuesta_active) ? 'opacity-50' : ''; ?>">
                        <div class="rounded-circle <?php echo $step_propuesta_active ? 'bg-primary text-white shadow-gold border-0' : ($step_propuesta_done ? 'bg-success text-white shadow-gold border-0' : 'bg-midnight border border-white-10 text-white-50'); ?> d-flex align-items-center justify-content-center p-1 mb-1"
                            style="width: 28px; height: 28px;">
                            <span
                                class="material-symbols-outlined x-small"><?php echo $step_propuesta_done && !$step_propuesta_active ? 'check' : 'payments'; ?></span>
                        </div>
                        <span class="text-white x-small fw-bold"><?= __('dashboard.step_proposal') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Skeleton Loaders -->
    <?php for ($i = 0; $i < 3; $i++): ?>
        <div class="col-md-4 skeleton-loader no-print">
            <div class="glass-morphism p-4 rounded-4 border-white-10 h-100">
                <div class="skeleton skeleton-text mb-3" style="width: 50%"></div>
                <div class="d-flex justify-content-between align-items-end">
                    <div class="skeleton skeleton-title mb-0" style="width: 40%"></div>
                    <div class="skeleton skeleton-text mb-0" style="width: 20%"></div>
                </div>
            </div>
        </div>
    <?php endfor; ?>

    <!-- Quick Stats in Admin KPI Style -->
    <div class="col-md-4">
        <div class="glass-morphism-premium p-4 rounded-4 h-100 position-relative overflow-hidden kpi-card">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <span class="material-symbols-outlined display-4 text-gradient">bolt</span>
            </div>
            <p class="text-white-50 x-small fw-bold uppercase tracking-widest mb-1"><?= __('kpi.active_services') ?></p>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="text-white fw-black mb-0 display-6 kpi-value"><?php echo count($services); ?></h3>
                <span class="text-primary small fw-bold"><?= __('kpi.in_progress') ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-morphism-premium p-4 rounded-4 h-100 position-relative overflow-hidden kpi-card">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <span class="material-symbols-outlined display-4 text-gradient">confirmation_number</span>
            </div>
            <p class="text-white-50 x-small fw-bold uppercase tracking-widest mb-1"><?= __('kpi.requests') ?></p>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="text-white fw-black mb-0 display-6 kpi-value"><?php echo count($tickets); ?></h3>
                <span class="text-accent small fw-bold"><?= __('kpi.pending') ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-morphism-premium p-4 rounded-4 h-100 position-relative overflow-hidden kpi-card">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <span class="material-symbols-outlined display-4 text-gradient">credit_score</span>
            </div>
            <p class="text-white-50 x-small fw-bold uppercase tracking-widest mb-1"><?= __('kpi.unpaid_bills') ?></p>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="text-white fw-black mb-0 display-6 kpi-value"><?php echo $unpaid_count; ?></h3>
                <span
                    class="<?php echo $unpaid_count == 0 ? 'text-success' : 'text-danger'; ?> small fw-bold"><?php echo $unpaid_count == 0 ? __('dashboard.up_to_date') : __('dashboard.unpaid'); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Tickets & Active Services -->
    <div class="col-lg-8">
        <!-- Recent Tickets -->
        <div class="glass-morphism-premium rounded-5 overflow-hidden mb-4">
            <div class="p-4 border-bottom border-white-10 d-flex align-items-center justify-content-between">
                <h2 class="text-white h5 fw-black mb-0"><?= __('dashboard.recent_requests') ?></h2>
                <div class="d-flex gap-2">
                    <a href="<?php echo url('ticket'); ?>" class="btn btn-outline-light btn-sm px-3 fw-bold small"
                        style="min-width: 90px;"><?= __('dashboard.view_all') ?></a>
                    <a href="<?php echo url('ticket/request'); ?>" class="btn btn-primary btn-sm px-3 fw-bold small"
                        style="min-width: 120px;"><?= __('dashboard.new_request') ?></a>
                </div>
            </div>
            <div class="p-4">
                <?php if (empty($tickets)): ?>
                    <p class="text-white-50 text-center py-4 mb-0"><?= __('dashboard.no_recent_tickets') ?></p>
                <?php else: ?>
                    <div class="list-group list-group-flush border-0">
                        <!-- Skeletons -->
                        <?php for ($i = 0; $i < 3; $i++): ?>
                            <div
                                class="list-group-item bg-transparent border-white-5 p-3 px-0 d-flex align-items-center gap-3 skeleton-row no-print">
                                <div class="skeleton skeleton-avatar flex-shrink-0" style="width: 50px; height: 50px;"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton skeleton-text" style="width: 60%"></div>
                                    <div class="skeleton skeleton-text" style="width: 40%"></div>
                                </div>
                            </div>
                        <?php endfor; ?>

                        <?php foreach ($tickets as $ticket): ?>
                            <a href="<?php echo url('ticket/detail/' . $ticket['id']); ?>"
                                class="list-group-item bg-transparent border-white-5 p-3 px-0 d-flex align-items-center gap-3 hover-lift-sm transition-all text-decoration-none">
                                <div class="rounded-4 bg-steel d-flex align-items-center justify-content-center text-primary flex-shrink-0"
                                    style="width: 50px; height: 50px;">
                                    <span class="material-symbols-outlined">confirmation_number</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-white fw-bold mb-1"><?php echo $ticket['subject']; ?></h6>
                                    <p class="text-white-50 x-small mb-0 d-flex flex-wrap align-items-center gap-3">
                                        <span><?= __('common.search') == 'Search' ? 'Plan' : 'Plan' ?>: <span class="text-white"><?php echo $ticket['plan_name']; ?></span></span>
                                        <span><?= __('common.date') ?>: <span
                                                class="text-white"><?php echo date('d/m/Y', strtotime($ticket['created_at'])); ?></span></span>
                                    </p>
                                </div>
                                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                    <span
                                        class="badge border border-white-10 text-white-50 fw-normal x-small px-2 font-monospace">
                                        <?php echo $ticket['ticket_number']; ?>
                                    </span>
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 uppercase x-small tracking-widest fw-black">
                                        <?php echo translateStatus($ticket['status']); ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Services -->
        <div class="glass-morphism-premium rounded-5 overflow-hidden mb-4">
            <div class="p-4 border-bottom border-white-10 d-flex align-items-center justify-content-between">
                <h2 class="text-white h5 fw-black mb-0"><?= __('dashboard.my_services') ?></h2>
                <a href="<?php echo url('project/workspace'); ?>"
                    class="btn btn-outline-primary btn-sm px-3 fw-bold small"><?= __('dashboard.view_all') ?></a>
            </div>
            <div class="p-0">
                <?php if (empty($services)): ?>
                    <div class="p-5 text-center">
                        <span class="material-symbols-outlined display-3 text-white-10 mb-3">layers_clear</span>
                        <p class="text-white-50"><?= __('dashboard.no_active_services') ?></p>
                        <a href="<?php echo url('ticket/request'); ?>" class="btn btn-primary btn-sm px-4 mt-2"><?= __('dashboard.hire_now') ?></a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead class="bg-deep-black">
                                <tr>
                                    <th class="border-white-10 p-4 small uppercase tracking-widest text-white-50"><?= __('common.search') == 'Search' ? 'Service' : 'Servicio' ?>
                                    </th>
                                    <th class="border-white-10 p-4 small uppercase tracking-widest text-white-50"><?= __('common.search') == 'Search' ? 'Plan' : 'Plan' ?></th>
                                    <th class="border-white-10 p-4 small uppercase tracking-widest text-white-50"><?= __('common.status') ?>
                                    </th>
                                    <th class="border-white-10 p-4 small uppercase tracking-widest text-white-50"><?= __('common.actions') ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                    <tr>
                                        <td class="p-4 align-middle">
                                            <div class="fw-bold text-white mb-1">
                                                <?php echo $service['name']; ?>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1 bg-white-5"
                                                    style="height: 4px; max-width: 100px;">
                                                    <div class="progress-bar bg-accent" role="progressbar"
                                                        style="width: <?php echo $service['progress_percent']; ?>%"
                                                        aria-valuenow="<?php echo $service['progress_percent']; ?>"
                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span
                                                    class="text-white-50 x-small fw-bold"><?php echo $service['progress_percent']; ?>%</span>
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <span class="badge border border-primary text-primary px-3 py-2">
                                                <?php echo $service['plan_name']; ?>
                                            </span>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-2 small">
                                                <?php echo translateStatus($service['status']); ?>
                                            </span>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <a href="<?php echo url('project/workspace'); ?>"
                                                class="btn btn-outline-light btn-sm rounded-3">
                                                <span class="material-symbols-outlined fs-6">arrow_forward</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <div class="glass-morphism-premium p-4 py-md-5 rounded-5 mb-4 text-center transition-all hover-lift">
            <div class="rounded-circle shadow-gold mx-auto d-flex align-items-center justify-content-center text-white mb-4"
                style="width: 80px; height: 80px; background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 448 512" fill="white">
                    <path
                        d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.4 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-5.5-2.8-23.2-8.5-44.2-27.1-16.4-14.6-27.4-32.7-30.6-38.1-3.2-5.5-.3-8.4 2.4-11.2 2.5-2.5 5.5-6.4 8.3-9.7 2.8-3.2 3.7-5.5 5.6-9.2 1.9-3.7 1-6.9-.5-9.7-1.4-2.8-12.4-29.8-17-41.1-4.5-10.9-9.1-9.4-12.4-9.6-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 13.2 5.7 23.5 9.2 31.6 11.8 13.3 4.2 25.4 3.6 35 2.2 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.7z" />
                </svg>
            </div>
            <h5 class="text-white fw-bold mb-3"><?= __('dashboard.priority_support') ?></h5>
            <p class="text-white-50 small mb-4 px-3"><?= __('dashboard.priority_support_help') ?></p>
            <a href="<?php echo url('dashboard/urgentSupport'); ?>"
                class="btn btn-primary px-4 py-2 fw-bold uppercase x-small shadow-gold"><?= __('dashboard.live_chat') ?></a>
        </div>
    </div>
</div>

<?php /* Chart script removed */ ?>

<style>

    .table-dark {
        --bs-table-bg: transparent;
        --bs-table-color: rgba(255, 255, 255, 0.8);
    }

    .table-dark thead th {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
</style>
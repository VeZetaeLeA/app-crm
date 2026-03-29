<div class="glass-morphism rounded-5 border-white-10 overflow-hidden h-100">
    <div class="p-4 border-bottom border-white-10 d-flex align-items-center justify-content-between bg-white-5">
        <h2 class="text-white h5 fw-black mb-0">Solicitudes Recientes</h2>
        <div class="input-group input-group-sm w-auto">
            <span class="input-group-text bg-midnight border-white-10 text-white-50">
                <span class="material-symbols-outlined fs-6">search</span>
            </span>
            <input type="text" id="adminSearchInput" class="form-control bg-steel border-white-10 text-white"
                placeholder="Filtrar...">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle">
            <thead class="bg-deep-black">
                <tr class="x-small uppercase text-white-50 tracking-widest">
                    <th class="p-4 border-0">Acciones</th>
                    <th class="p-4 border-0">Nº Ticket</th>
                    <th class="p-4 border-0">Cliente</th>
                    <th class="p-4 border-0">Servicio / Plan</th>
                    <th class="p-4 border-0">Estado</th>
                    <th class="p-4 border-0">Fecha</th>
                </tr>
            </thead>
            <tbody id="adminTicketsTable">
                <!-- Skeleton Loader Rows (Removed via JS) -->
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <tr class="skeleton-row no-print">
                        <td class="p-4">
                            <div class="skeleton skeleton-text" style="width: 40%"></div>
                            <div class="skeleton skeleton-text" style="width: 80%"></div>
                        </td>
                        <td class="p-4">
                            <div class="skeleton skeleton-text" style="width: 60%"></div>
                        </td>
                        <td class="p-4 text-end">
                            <div class="skeleton skeleton-btn ms-auto"></div>
                        </td>
                    </tr>
                <?php endfor; ?>

                <?php foreach ($tickets as $ticket): ?>
                    <tr class="ticket-row"
                        data-search="<?php echo strtolower($ticket['ticket_number'] . ' ' . $ticket['client_name'] . ' ' . $ticket['subject']); ?>">
                        <td class="p-4">
                            <a href="<?php echo url('ticket/detail/' . $ticket['id']); ?>"
                                class="btn btn-outline-white btn-sm rounded-pill px-3 border-white-10 x-small uppercase fw-bold">
                                Ver Detalle
                            </a>
                        </td>
                        <td class="p-4">
                            <span class="fw-black text-primary font-monospace">
                                <?php echo htmlspecialchars($ticket['ticket_number']); ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-white small fw-bold">
                                    <?php echo htmlspecialchars($ticket['client_name']); ?>
                                </span>
                                <?php
                                $score = $ticket['lead_score'] ?? 0;
                                $scoreClass = $score >= 75 ? 'bg-gold text-deep-black' : ($score >= 40 ? 'bg-primary' : 'bg-white-10 text-white-50');
                                ?>
                                <span class="badge <?php echo $scoreClass; ?> rounded-pill x-small px-2 py-1 fw-bold"
                                    style="font-size: 0.65rem;" title="Lead Intelligence Score">
                                    <?php echo $score; ?> pts
                                    <?php if ($score >= 75): ?> 🔥<?php endif; ?>
                                </span>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="small text-white">
                                <?php echo htmlspecialchars($ticket['service_name'] ?? 'Sin Servicio'); ?>
                            </div>
                            <div class="x-small text-white-50">
                                <?php echo htmlspecialchars($ticket['plan_name'] ?? 'Plan Base'); ?>
                            </div>
                        </td>
                        <td class="p-4">
                            <?php
                            $statusClass = [
                                'open' => 'bg-danger-subtle text-danger',
                                'in_analysis' => 'bg-warning-subtle text-warning',
                                'budget_sent' => 'bg-info-subtle text-info',
                                'budget_approved' => 'bg-success-subtle text-success',
                                'budget_rejected' => 'bg-danger-subtle text-danger',
                                'invoiced' => 'bg-info-subtle text-info',
                                'payment_pending' => 'bg-warning-subtle text-warning',
                                'active' => 'bg-success-subtle text-success',
                                'resolved' => 'bg-success-subtle text-success',
                                'closed' => 'bg-secondary-subtle text-white-50',
                                'void' => 'bg-dark text-white'
                            ];
                            $cls = $statusClass[$ticket['status']] ?? 'bg-white-10';
                            ?>
                            <span class="badge <?php echo $cls; ?> x-small uppercase fw-bold tracking-tighter px-2 py-1">
                                <?php echo translateStatus($ticket['status']); ?>
                            </span>
                            <?php if (isset($ticket['is_at_risk']) && $ticket['is_at_risk']): ?>
                                <span class="badge border border-warning text-warning x-small fw-bold px-2 py-1 ms-2"
                                    title="<?php echo htmlspecialchars($ticket['risk_reason']); ?>">
                                    <span class="material-symbols-outlined fs-6 align-middle me-1"
                                        style="font-size: 14px !important;">warning</span>
                                    RIESGO ANS
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <span class="text-white-50 x-small">
                                <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="6" class="p-5 text-center text-white-50 italic">
                            No hay solicitudes pendientes que requieran acción inmediata.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="row g-4">
    <div class="col-12 d-flex align-items-center justify-content-between mb-2">
        <div>
            <h2 class="text-white fw-black mb-1">Centro de Gestión de Tickets 🎫</h2>
            <p class="text-white-50">Monitorea y responde a todas las solicitudes de servicio en tiempo real.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-white btn-sm px-4 rounded-pill border-white-10 text-white-50">Descargar
                Reporte</button>
            <a href="<?php echo url('ticket/request'); ?>"
                class="btn btn-primary btn-sm px-4 fw-bold rounded-pill shadow-gold">Nuevo Ticket</a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="col-12">
        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <div class="glass-morphism p-3 rounded-4 border-white-10">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1">Total Tickets</p>
                    <h3 class="text-white mb-0">
                        <?php echo count($tickets); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-morphism p-3 rounded-4 border-white-10">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1">Pendientes</p>
                    <h3 class="text-warning mb-0">
                        <?php echo count(array_filter($tickets, fn($t) => $t['status'] == 'open')); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-morphism p-3 rounded-4 border-white-10">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1">En Proceso</p>
                    <h3 class="text-info mb-0">
                        <?php echo count(array_filter($tickets, fn($t) => $t['status'] == 'in_progress')); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-morphism p-3 rounded-4 border-white-10">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1">Cerrados</p>
                    <h3 class="text-success mb-0">
                        <?php echo count(array_filter($tickets, fn($t) => $t['status'] == 'closed')); ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="col-12">
        <div class="glass-morphism rounded-5 border-white-10 overflow-hidden shadow-2xl">
            <div class="p-4 border-bottom border-white-10 bg-white-5 d-flex align-items-center justify-content-between">
                <h5 class="text-white h6 mb-0 fw-bold uppercase tracking-widest">Listado de Solicitudes</h5>
                <div class="input-group input-group-sm w-auto">
                    <span class="input-group-text bg-steel border-white-10 text-white-50">
                        <span class="material-symbols-outlined fs-6">filter_list</span>
                    </span>
                    <select class="form-select bg-steel border-white-10 text-white x-small fw-bold uppercase">
                        <option value="">Todos los estados</option>
                        <option value="open">Abiertos</option>
                        <option value="in_progress">En Proceso</option>
                        <option value="resolved">Resueltos</option>
                        <option value="closed">Cerrados</option>
                        <option value="void">Anulados</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-deep-black">
                        <tr class="x-small uppercase text-white-50 tracking-widest">
                            <th class="p-4 border-0 text-start">Acciones</th>
                            <th class="p-4 border-0">Nº Ticket</th>
                            <th class="p-4 border-0">Cliente</th>
                            <th class="p-4 border-0">Servicio / Plan</th>
                            <th class="p-4 border-0">Estado</th>
                            <th class="p-4 border-0">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td class="p-4 text-start">
                                    <a href="<?php echo url('ticket/detail/' . $t['id']); ?>"
                                        class="btn btn-outline-white btn-sm rounded-pill px-3 border-white-10 x-small uppercase fw-bold">
                                        Ver Detalle
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
                                            <?php echo $score; ?> pts
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
                                        'open' => 'bg-danger-subtle text-danger',
                                        'in_progress' => 'bg-info-subtle text-info',
                                        'resolved' => 'bg-success-subtle text-success',
                                        'closed' => 'bg-secondary-subtle text-white-50',
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
                                            RIESGO ANS
                                        </span>
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
                                <td colspan="6" class="p-5 text-center text-white-50 italic">
                                    No hay tickets registrados en el sistema.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-white-5 border-top border-white-10 text-center">
                <span class="text-white-50 x-small uppercase tracking-widest">
                    Mostrando
                    <?php echo count($tickets); ?> registros filtrados
                </span>
            </div>
        </div>
    </div>
</div>
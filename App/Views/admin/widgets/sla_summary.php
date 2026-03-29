<?php
/** @var array $stats */
$sla = $stats['sla'] ?? ['breached' => 0, 'at_risk' => 0, 'on_time' => 0, 'total_active' => 0];
$total = $sla['total_active'] ?: 1; // Evitar división por cero
$breachedPct = round(($sla['breached'] / $total) * 100);
$atRiskPct = round(($sla['at_risk'] / $total) * 100);
$onTimePct = 100 - $breachedPct - $atRiskPct;
?>
<div class="glass-morphism border-white-10 p-4 rounded-4 h-100">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="h5 fw-black text-white mb-0">Monitor de ANS (SLA) ⏱️</h3>
            <p class="text-white-50 x-small mb-0">Cumplimiento de tiempos de resolución.</p>
        </div>
        <span class="material-symbols-outlined text-elegant-gold fs-4">timer</span>
    </div>

    <div class="sla-breakdown">
        <!-- Breached -->
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-white-70">Vencidos (Breached)</span>
                <span class="small fw-bold text-danger"><?php echo $sla['breached']; ?></span>
            </div>
            <div class="progress bg-white-05" style="height: 6px;">
                <div class="progress-bar bg-danger" style="width: <?php echo $breachedPct; ?>%"></div>
            </div>
        </div>

        <!-- At Risk -->
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-white-70">En Riesgo (< 12h)</span>
                <span class="small fw-bold text-warning"><?php echo $sla['at_risk']; ?></span>
            </div>
            <div class="progress bg-white-05" style="height: 6px;">
                <div class="progress-bar bg-warning" style="width: <?php echo $atRiskPct; ?>%"></div>
            </div>
        </div>

        <!-- On Time -->
        <div class="mb-4">
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-white-70">A Tiempo</span>
                <span class="small fw-bold text-success"><?php echo $sla['on_time']; ?></span>
            </div>
            <div class="progress bg-white-05" style="height: 6px;">
                <div class="progress-bar bg-success" style="width: <?php echo $onTimePct; ?>%"></div>
            </div>
        </div>
    </div>

    <div class="mt-auto pt-3 border-top border-white-10 text-center">
        <a href="<?php echo url('ticket/kanban'); ?>" class="btn btn-outline-light btn-xs rounded-pill px-3 border-white-10 hover-gold text-decoration-none">
            <span class="x-small fw-bold">Ver Radar de Urgencias</span>
        </a>
    </div>
</div>

<?php
/**
 * Vista Timeline (Gantt CSS) de Proyecto — SPRINT 2.4
 * Visualiza el ciclo de vida de cada entregable en una línea de tiempo.
 */
?>
<div class="row g-4 mb-5">
    <!-- Header -->
    <div class="col-12">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <a href="<?php echo url('project/workspace'); ?>"
                    class="text-accent x-small fw-bold text-decoration-none d-flex align-items-center gap-1 mb-2">
                    <span class="material-symbols-outlined fs-6">arrow_back</span> Volver a Workspaces
                </a>
                <h2 class="text-white fw-black mb-1">
                    Timeline <span class="text-gradient"><?php echo htmlspecialchars($service['name']); ?></span>
                </h2>
                <p class="text-white-50 small mb-0">
                    Cliente: <span class="text-white"><?php echo htmlspecialchars($service['client_name']); ?></span>
                    &nbsp;|&nbsp; Plan: <span class="text-gold"><?php echo htmlspecialchars($service['plan_name']); ?></span>
                    &nbsp;|&nbsp; Inicio: <span class="text-white"><?php echo date('d/m/Y', strtotime($service['start_date'])); ?></span>
                </p>
            </div>
            <a href="<?php echo url('project/manage/' . $service['id']); ?>"
                class="btn btn-outline-light btn-sm rounded-pill border-white-10 px-3">
                <span class="material-symbols-outlined fs-6 align-middle me-1">folder_shared</span> Gestionar Workspace
            </a>
        </div>
    </div>

    <!-- KPI Summary -->
    <?php
    $totalD    = count($deliverables);
    $approved  = count(array_filter($deliverables, fn($d) => ($d['status'] ?? '') === 'approved'));
    $pending   = count(array_filter($deliverables, fn($d) => ($d['status'] ?? '') === 'pending_review'));
    $rejected  = count(array_filter($deliverables, fn($d) => ($d['status'] ?? '') === 'rejected'));
    $scopeTotal = (int)($service['total_deliverables'] ?? 0);
    $pct       = $scopeTotal > 0 ? round(($approved / $scopeTotal) * 100) : 0;
    ?>
    <div class="col-12">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="glass-morphism-premium p-3 rounded-4 text-center">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1">Entregables</p>
                    <h3 class="text-white fw-black mb-0"><?php echo $totalD; ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-morphism-premium p-3 rounded-4 text-center">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1">Aprobados</p>
                    <h3 class="text-success fw-black mb-0"><?php echo $approved; ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-morphism-premium p-3 rounded-4 text-center">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1">En Revisión</p>
                    <h3 class="text-warning fw-black mb-0"><?php echo $pending; ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-morphism-premium p-3 rounded-4 text-center">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1">Progreso Alcance</p>
                    <h3 class="text-primary fw-black mb-0"><?php echo $pct; ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar Global -->
    <div class="col-12">
        <div class="glass-morphism p-4 rounded-5 border-white-10">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 x-small fw-bold uppercase tracking-widest">Progreso Global del Proyecto</span>
                <span class="text-white small fw-bold"><?php echo $approved; ?> / <?php echo max($scopeTotal, $totalD); ?> aprobados</span>
            </div>
            <div class="progress rounded-pill" style="height: 12px; background: rgba(255,255,255,0.08);">
                <div class="progress-bar" role="progressbar"
                    style="width: <?php echo $pct; ?>%; background: linear-gradient(90deg, #D4AF37, #30C5FF);"
                    aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span class="text-white-25 x-small">Inicio: <?php echo date('d/m/Y', strtotime($service['start_date'])); ?></span>
                <?php if (!empty($service['end_date'])): ?>
                    <span class="text-white-25 x-small">Fin estimado: <?php echo date('d/m/Y', strtotime($service['end_date'])); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="col-12">
        <div class="glass-morphism rounded-5 border-white-10 p-4">
            <h5 class="text-white fw-bold mb-4 uppercase tracking-widest x-small d-flex align-items-center gap-2">
                <span class="material-symbols-outlined fs-5 text-accent">timeline</span>
                Línea de Tiempo de Entregables
            </h5>

            <?php if (empty($deliverables)): ?>
                <div class="text-center py-5">
                    <span class="material-symbols-outlined display-3 text-white-10 d-block mb-3">hourglass_empty</span>
                    <p class="text-white-50">Aún no hay entregables cargados en este proyecto.</p>
                </div>
            <?php else: ?>
                <div class="timeline-container position-relative ps-4" style="border-left: 2px solid rgba(255,255,255,0.1);">
                    <?php foreach ($deliverables as $i => $d): ?>
                        <?php
                        $status = $d['status'] ?? 'pending_review';
                        $statusMap = [
                            'pending_review' => ['icon' => 'pending',      'color' => '#fbbf24', 'label' => 'Pendiente de Revisión', 'bg' => 'rgba(251,191,36,0.12)',  'border' => 'rgba(251,191,36,0.35)'],
                            'approved'       => ['icon' => 'check_circle', 'color' => '#22c55e', 'label' => 'Aprobado',             'bg' => 'rgba(34,197,94,0.12)',   'border' => 'rgba(34,197,94,0.35)'],
                            'rejected'       => ['icon' => 'cancel',       'color' => '#ef4444', 'label' => 'Rechazado',            'bg' => 'rgba(239,68,68,0.12)',   'border' => 'rgba(239,68,68,0.35)'],
                        ];
                        $sm = $statusMap[$status];
                        $uploadDate  = date('d M, Y · H:i', strtotime($d['created_at']));
                        $reviewDate  = !empty($d['reviewed_at']) ? date('d M, Y · H:i', strtotime($d['reviewed_at'])) : null;
                        $daysOpen    = $reviewDate
                            ? round((strtotime($d['reviewed_at']) - strtotime($d['created_at'])) / 86400)
                            : round((time() - strtotime($d['created_at'])) / 86400);
                        ?>
                        <div class="timeline-event position-relative mb-4 ps-4 animate-slide-in" style="animation-delay: <?php echo $i * 0.07; ?>s">
                            <!-- Dot -->
                            <div class="position-absolute d-flex align-items-center justify-content-center rounded-circle"
                                style="left: -21px; top: 8px; width: 34px; height: 34px; background: <?php echo $sm['bg']; ?>; border: 2px solid <?php echo $sm['color']; ?>; z-index: 1;">
                                <span class="material-symbols-outlined" style="font-size: 16px; color: <?php echo $sm['color']; ?>;"><?php echo $sm['icon']; ?></span>
                            </div>

                            <div class="rounded-4 p-4" style="background: <?php echo $sm['bg']; ?>; border: 1px solid <?php echo $sm['border']; ?>;">
                                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge fw-bold x-small px-2 py-1"
                                                style="background: <?php echo $sm['bg']; ?>; color: <?php echo $sm['color']; ?>; border: 1px solid <?php echo $sm['border']; ?>;">
                                                <?php echo $sm['label']; ?>
                                            </span>
                                            <span class="badge bg-white-5 text-white-50 x-small"><?php echo strtoupper($d['file_type']); ?></span>
                                        </div>
                                        <h5 class="text-white fw-bold mb-0"><?php echo htmlspecialchars($d['title']); ?></h5>
                                        <p class="text-white-50 x-small mb-0">v<?php echo $d['version']; ?> — <?php echo number_format($d['file_size'] / 1024, 1); ?> KB</p>
                                    </div>
                                    <a href="<?php echo url('project/download/' . $d['id']); ?>"
                                        class="btn btn-sm rounded-pill border-white-10 text-white-50 hover-gold transition-all"
                                        style="background: rgba(255,255,255,0.05);">
                                        <span class="material-symbols-outlined fs-6 align-middle">download</span>
                                    </a>
                                </div>

                                <?php if (!empty($d['description'])): ?>
                                    <p class="text-white-50 small mb-3"><?php echo htmlspecialchars($d['description']); ?></p>
                                <?php endif; ?>

                                <div class="row g-3">
                                    <div class="col-sm-4">
                                        <div class="rounded-3 p-2 px-3" style="background: rgba(0,0,0,0.3);">
                                            <p class="text-white-25 x-small mb-0 uppercase fw-bold">Subido por</p>
                                            <p class="text-white small mb-0 fw-bold"><?php echo htmlspecialchars($d['author_name']); ?></p>
                                            <p class="text-white-50 x-small mb-0"><?php echo $uploadDate; ?></p>
                                        </div>
                                    </div>
                                    <?php if ($reviewDate): ?>
                                        <div class="col-sm-4">
                                            <div class="rounded-3 p-2 px-3" style="background: rgba(0,0,0,0.3);">
                                                <p class="text-white-25 x-small mb-0 uppercase fw-bold">Revisado por</p>
                                                <p class="text-white small mb-0 fw-bold"><?php echo htmlspecialchars($d['reviewer_name'] ?? 'Cliente'); ?></p>
                                                <p class="text-white-50 x-small mb-0"><?php echo $reviewDate; ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-sm-4">
                                        <div class="rounded-3 p-2 px-3 text-center" style="background: rgba(0,0,0,0.3);">
                                            <p class="text-white-25 x-small mb-1 uppercase fw-bold"><?php echo $reviewDate ? 'Tiempo de Rev.' : 'Días abierto'; ?></p>
                                            <h4 class="fw-black mb-0" style="color: <?php echo $sm['color']; ?>;"><?php echo $daysOpen; ?></h4>
                                            <p class="text-white-50 x-small mb-0">días</p>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($d['review_notes'])): ?>
                                    <div class="mt-3 rounded-3 p-3" style="background: rgba(0,0,0,0.25); border-left: 3px solid <?php echo $sm['color']; ?>;">
                                        <p class="text-white-50 x-small fw-bold uppercase mb-1">
                                            <span class="material-symbols-outlined align-middle" style="font-size:13px;">notes</span>
                                            Notas de Revisión:
                                        </p>
                                        <p class="text-white small mb-0"><?php echo htmlspecialchars($d['review_notes']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Final marker -->
                    <div class="position-relative ps-4 mb-2">
                        <div class="position-absolute d-flex align-items-center justify-content-center rounded-circle"
                            style="left: -14px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; background: rgba(212,175,55,0.3); border: 2px solid rgba(212,175,55,0.5);">
                        </div>
                        <span class="text-white-25 x-small italic">Fin de la línea de tiempo</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(-20px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    .animate-slide-in {
        animation: slideInLeft 0.4s ease-out both;
    }
    .text-gold { color: var(--elegant-gold) !important; }
    .hover-gold:hover { color: var(--elegant-gold) !important; }
    .transition-all { transition: all 0.2s ease; }
</style>

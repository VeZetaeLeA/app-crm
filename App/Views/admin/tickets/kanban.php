<?php
/**
 * Vista Kanban de Tickets — SPRINT 2.3
 * Drag & Drop con SortableJS + endpoint AJAX updateStatus
 */
?>
<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex align-items-center justify-content-between mb-2 flex-wrap gap-3">
        <div>
            <h2 class="text-white fw-black mb-1">Kanban de <span class="text-gradient">Tickets</span> 🗂️</h2>
            <p class="text-white-50 mb-0">Arrastra las tarjetas para actualizar el estado en tiempo real.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo url('ticket'); ?>" class="btn btn-outline-light btn-sm rounded-pill border-white-10 px-3">
                <span class="material-symbols-outlined fs-6 align-middle me-1">list</span> Vista Listado
            </a>
            <a href="<?php echo url('ticket/request'); ?>" class="btn btn-primary btn-sm px-4 fw-bold rounded-pill shadow-gold">
                Nuevo Ticket
            </a>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="col-12">
        <div class="row g-3">
            <?php
            $columns = [
                'open'         => ['label' => 'Abiertos',   'color' => \Core\Config::get('ui.danger_color', '#ef4444'), 'icon' => 'inbox'],
                'in_progress'  => ['label' => 'En Proceso', 'color' => \Core\Config::get('ui.info_color', '#30C5FF'), 'icon' => 'autorenew'],
                'resolved'     => ['label' => 'Resueltos',  'color' => \Core\Config::get('ui.success_color', '#10b981'), 'icon' => 'check_circle'],
                'closed'       => ['label' => 'Cerrados',   'color' => '#6b7280', 'icon' => 'lock'],
            ];
            foreach ($columns as $status => $cfg):
                $count = count(array_filter($tickets, fn($t) => $t['status'] === $status));
            ?>
                <div class="col-6 col-md-3">
                    <div class="glass-morphism-premium p-3 rounded-4">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="material-symbols-outlined fs-5" style="color:<?php echo $cfg['color']; ?>"><?php echo $cfg['icon']; ?></span>
                            <span class="text-white-50 x-small fw-bold uppercase"><?php echo $cfg['label']; ?></span>
                        </div>
                        <h3 class="fw-black mb-0" style="color:<?php echo $cfg['color']; ?>"><?php echo $count; ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="col-12">
        <div class="kanban-board d-flex gap-3 overflow-x-auto pb-3" style="min-height: 70vh;">

            <?php
            $allStatuses = [
                'open'         => ['label' => 'Abierto',    'color' => 'rgba(239,68,68,0.1)',   'border' => 'rgba(239,68,68,0.3)',   'badge' => 'bg-danger-subtle text-danger',   'icon' => 'inbox'],
                'in_analysis'  => ['label' => 'En Análisis','color' => 'rgba(92,77,125,0.1)',  'border' => 'rgba(92,77,125,0.3)',  'badge' => 'bg-warning-subtle text-warning', 'icon' => 'manage_search'],
                'in_progress'  => ['label' => 'En Proceso', 'color' => 'rgba(48,197,255,0.1)',  'border' => 'rgba(48,197,255,0.3)',  'badge' => 'bg-info-subtle text-info',       'icon' => 'autorenew'],
                'resolved'     => ['label' => 'Resuelto',   'color' => 'rgba(16,185,129,0.1)',   'border' => 'rgba(16,185,129,0.3)',   'badge' => 'bg-success-subtle text-success', 'icon' => 'check_circle'],
                'closed'       => ['label' => 'Cerrado',    'color' => 'rgba(10,10,10,0.08)','border' => 'rgba(255,255,255,0.1)','badge' => 'bg-secondary-subtle text-secondary','icon' => 'lock'],
            ];

            foreach ($allStatuses as $status => $cfg):
                $colTickets = array_filter($tickets, fn($t) => $t['status'] === $status);
                $count = count($colTickets);
            ?>
                <!-- Column: <?php echo $cfg['label']; ?> -->
                <div class="kanban-column flex-shrink-0" style="width: 300px; min-width: 280px;">
                    <!-- Column Header -->
                    <div class="rounded-4 p-3 mb-3 d-flex align-items-center justify-content-between"
                        style="background: <?php echo $cfg['color']; ?>; border: 1px solid <?php echo $cfg['border']; ?>;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined fs-5" style="color:<?php echo preg_replace('/rgba\((\d+),(\d+),(\d+).*/', 'rgb($1,$2,$3)', $cfg['border']); ?>"><?php echo $cfg['icon']; ?></span>
                            <span class="text-white fw-bold small uppercase tracking-widest"><?php echo $cfg['label']; ?></span>
                        </div>
                        <span class="badge <?php echo $cfg['badge']; ?> rounded-pill fw-black"><?php echo $count; ?></span>
                    </div>

                    <!-- Drop zone -->
                    <div class="kanban-drop-zone d-flex flex-column gap-2"
                        data-status="<?php echo $status; ?>"
                        style="min-height: 200px; border: 2px dashed transparent; border-radius: 16px; padding: 8px; transition: all 0.2s;">

                        <?php foreach ($colTickets as $t): ?>
                            <?php
                            $score = $t['lead_score'] ?? 0;
                            $scoreColor = $score >= 75 ? 'var(--vzl-color-gold)' : ($score >= 40 ? 'var(--vzl-color-info)' : 'rgba(255,255,255,0.3)');
                            ?>
                            <div class="kanban-card rounded-4 p-3 cursor-grab"
                                data-ticket-id="<?php echo $t['id']; ?>"
                                data-current-status="<?php echo $t['status']; ?>"
                                style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); transition: all 0.2s; user-select: none;">

                                <!-- Card Header -->
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-black font-monospace x-small" style="color: var(--vzl-color-info);"><?php echo $t['ticket_number']; ?></span>
                                    <?php if ($score > 0): ?>
                                        <span class="badge rounded-pill fw-bold x-small" style="background: rgba(0,0,0,0.3); color: <?php echo $scoreColor; ?>; border: 1px solid <?php echo $scoreColor; ?>; font-size: 0.6rem;">
                                            <?php echo $score; ?> pts
                                            <?php if ($score >= 75): ?> 🔥<?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Subject -->
                                <p class="text-white fw-bold small mb-2 line-clamp-2" style="line-height: 1.4;"><?php echo htmlspecialchars($t['subject']); ?></p>

                                <!-- Client / Service -->
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="rounded-circle bg-white-5 d-flex align-items-center justify-content-center fw-bold text-accent"
                                        style="width: 24px; height: 24px; font-size: 0.65rem; flex-shrink:0;">
                                        <?php echo strtoupper(substr($t['client_name'], 0, 1)); ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-white x-small mb-0 fw-bold text-truncate"><?php echo htmlspecialchars($t['client_name']); ?></p>
                                        <p class="text-white-50 x-small mb-0 text-truncate" style="font-size: 0.65rem;"><?php echo htmlspecialchars($t['service_name'] ?? ''); ?></p>
                                    </div>
                                </div>

                                <!-- Risk Badge -->
                                <?php if (isset($t['is_at_risk']) && $t['is_at_risk']): ?>
                                    <div class="rounded-3 px-2 py-1 mb-2 d-flex align-items-center gap-1"
                                        style="background: rgba(255,193,7,0.1); border: 1px solid rgba(255,193,7,0.3);">
                                        <span class="material-symbols-outlined" style="font-size:13px; color:#ffc107;">warning</span>
                                        <span class="x-small fw-bold" style="color:#ffc107; font-size:0.6rem;">RIESGO ANS</span>
                                    </div>
                                <?php endif; ?>

                                <!-- Footer -->
                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-white-10">
                                    <span class="text-white-25 x-small"><?php echo date('d/m', strtotime($t['created_at'])); ?></span>
                                    <a href="<?php echo url('ticket/detail/' . $t['id']); ?>"
                                        class="text-primary x-small text-decoration-none fw-bold hover-gold transition-all">
                                        Ver <span class="material-symbols-outlined align-middle" style="font-size:12px;">open_in_new</span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($count === 0): ?>
                            <div class="empty-column-hint text-center py-4 text-white-25 x-small italic" style="pointer-events:none;">
                                <span class="material-symbols-outlined d-block mb-1" style="font-size:32px; opacity:0.3;"><?php echo $cfg['icon']; ?></span>
                                Sin tickets
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Toast de actualización Kanban -->
<div id="kanban-toast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; display:none;">
    <div class="toast show align-items-center text-white border-0 rounded-4" id="kanban-toast-el" role="alert">
        <div class="d-flex px-3 py-2 align-items-center gap-2">
            <span class="material-symbols-outlined fs-5" id="kanban-toast-icon">check_circle</span>
            <span id="kanban-toast-msg" class="small fw-bold"></span>
            <button type="button" class="btn-close btn-close-white ms-auto" onclick="document.getElementById('kanban-toast').style.display='none'"></button>
        </div>
    </div>
</div>

<style>
    .kanban-board::-webkit-scrollbar { height: 6px; }
    .kanban-board::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 3px; }
    .kanban-board::-webkit-scrollbar-thumb { background: var(--vzl-color-gold); border-radius: 3px; }

    .kanban-card {
        cursor: grab;
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .kanban-card:hover {
        background: rgba(255,255,255,0.08) !important;
        border-color: var(--vzl-color-gold) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }
    .kanban-card.sortable-ghost {
        opacity: 0.3;
        transform: rotate(2deg);
    }
    .kanban-card.sortable-drag {
        box-shadow: 0 20px 60px rgba(0,0,0,0.6) !important;
        transform: rotate(1deg) scale(1.03);
    }
    .kanban-drop-zone.drag-over {
        border-color: var(--vzl-color-gold) !important;
        background: rgba(212,175,55,0.04);
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .cursor-grab { cursor: grab; }
</style>

<!-- Sortable.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function() {
    const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    const UPDATE_URL = '<?php echo url('ticket/updateStatus'); ?>';

    function showKanbanToast(msg, type = 'success') {
        const wrap  = document.getElementById('kanban-toast');
        const el    = document.getElementById('kanban-toast-el');
        const icon  = document.getElementById('kanban-toast-icon');
        const msgEl = document.getElementById('kanban-toast-msg');

        const config = {
            success: { bg: 'bg-success', icon: 'check_circle' },
            error:   { bg: 'bg-danger',  icon: 'error' },
            info:    { bg: 'bg-primary', icon: 'info' },
        };
        const c = config[type] || config.info;
        el.className = `toast show align-items-center text-white border-0 rounded-4 ${c.bg}`;
        icon.textContent = c.icon;
        msgEl.textContent = msg;
        wrap.style.display = 'block';
        setTimeout(() => wrap.style.display = 'none', 3500);
    }

    document.querySelectorAll('.kanban-drop-zone').forEach(zone => {
        Sortable.create(zone, {
            group: 'kanban-tickets',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass:  'sortable-drag',
            onStart(evt) {
                document.querySelectorAll('.kanban-drop-zone').forEach(z => z.classList.add('drag-over'));
            },
            onEnd(evt) {
                document.querySelectorAll('.kanban-drop-zone').forEach(z => z.classList.remove('drag-over'));

                const card       = evt.item;
                const newStatus  = evt.to.dataset.status;
                const ticketId   = card.dataset.ticketId;
                const oldStatus  = card.dataset.currentStatus;

                if (newStatus === oldStatus) return;

                // Optimistic UI update
                card.dataset.currentStatus = newStatus;

                // AJAX – updateStatus endpoint
                const formData = new FormData();
                formData.append('_token',    CSRF_TOKEN);
                formData.append('ticket_id', ticketId);
                formData.append('status',    newStatus);

                fetch(UPDATE_URL, {
                    method: 'POST',
                    body:   formData,
                    redirect: 'manual',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => {
                    if (res.ok || res.type === 'opaqueredirect') {
                        // Update column counters
                        updateColumnCounts();
                        showKanbanToast(`Ticket movido a "${evt.to.closest('.kanban-column').querySelector('.text-white.fw-bold').textContent.trim()}"`, 'success');
                    } else {
                        throw new Error('Server error');
                    }
                })
                .catch(() => {
                    showKanbanToast('Error al actualizar el ticket. Por favor, recarga la página.', 'error');
                    // Revert: move card back to original column
                    const originalZone = document.querySelector(`.kanban-drop-zone[data-status="${oldStatus}"]`);
                    if (originalZone) {
                        originalZone.insertBefore(card, originalZone.firstChild);
                        card.dataset.currentStatus = oldStatus;
                        updateColumnCounts();
                    }
                });
            }
        });
    });

    function updateColumnCounts() {
        document.querySelectorAll('.kanban-column').forEach(col => {
            const zone  = col.querySelector('.kanban-drop-zone');
            const cards = zone.querySelectorAll('.kanban-card').length;
            const badge = col.querySelector('.badge.rounded-pill');
            if (badge) badge.textContent = cards;

            const hint = zone.querySelector('.empty-column-hint');
            if (hint) hint.style.display = cards === 0 ? 'block' : 'none';
        });
    }
})();
</script>

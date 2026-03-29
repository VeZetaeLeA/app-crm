<div class="glass-morphism-premium p-4 rounded-5 h-100 insight-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white h5 fw-black mb-0">Recomendaciones IA ⚡</h2>
    </div>

    <div class="d-flex flex-column gap-3">
        <?php if (empty($stats['insights'])): ?>
            <div class="text-center p-4">
                <p class="text-white-50 x-small fw-bold">No se detectaron alertas críticas hoy. ¡Buen trabajo!</p>
            </div>
        <?php else: ?>
            <?php foreach ($stats['insights'] as $insight): ?>
                <div
                    class="insight-alert-item glass-morphism border-white-5 p-3 rounded-4 d-flex align-items-center justify-content-between hover-glow transition-all">
                    <div class="d-flex align-items-center gap-3">
                        <div class="insight-icon-container p-2 rounded-3 bg-white-5">
                            <?php if ($insight['type'] == 'alert'): ?>
                                <span class="material-symbols-outlined text-danger fs-5">warning</span>
                            <?php elseif ($insight['type'] == 'recommendation'): ?>
                                <span class="material-symbols-outlined text-warning fs-5">lightbulb</span>
                            <?php else: ?>
                                <span class="material-symbols-outlined text-success fs-5">rocket_launch</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-white small fw-bold mb-1">
                                <?php echo htmlspecialchars($insight['message']); ?>
                            </p>
                            <span class="x-small fw-bold uppercase tracking-widest px-2 py-1 rounded-pill bg-white-5 <?php
                            echo $insight['level'] == 'high' ? 'text-danger' : ($insight['level'] == 'medium' ? 'text-warning' : 'text-primary');
                            ?>">
                                <?php echo strtoupper($insight['level']); ?>
                            </span>
                        </div>
                    </div>
                    <?php if (isset($insight['action_url'])): ?>
                        <a href="<?php echo url($insight['action_url']); ?>"
                            class="btn btn-outline-light btn-xs rounded-pill px-3 py-1 fw-bold border-white-10 hover-gold transition-all no-print"
                            style="font-size: 10px;">
                            <?php echo $insight['action_label']; ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .insight-alert-item:hover {
        background: rgba(255, 255, 255, 0.05) !important;
        transform: translateX(5px);
    }

    .hover-gold:hover {
        background: var(--elegant-gold) !important;
        color: var(--deep-black) !important;
        border-color: var(--elegant-gold) !important;
    }
</style>
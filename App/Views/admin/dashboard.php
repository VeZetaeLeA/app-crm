<div class="row g-4 mb-5" id="adminDashboardContent">
    <div class="col-12 d-flex align-items-center justify-content-between mb-2 no-print">
        <div>
            <h2 class="text-white fw-black mb-1"><?= __('dashboard.admin_title') ?></h2>
            <p class="text-white-50"><?= __('dashboard.admin_subtitle', ['company' => \Core\Config::get('business.company_name')]) ?></p>
        </div>
        <div class="d-flex gap-2">
            <button type="button"
                class="btn btn-outline-light btn-sm px-4 rounded-pill fw-bold border-white-10 d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#configDashboardModal">
                <span class="material-symbols-outlined fs-6">settings</span>
                <?= __('dashboard.customize') ?>
            </button>
            <a href="<?php echo url('ticket'); ?>"
                class="btn btn-primary btn-sm px-4 rounded-pill fw-bold shadow-gold d-flex align-items-center gap-2">
                <span class="material-symbols-outlined fs-6">confirmation_number</span>
                <?= __('dashboard.view_tickets') ?>
            </a>
        </div>
    </div>

    <!-- Modal de Configuración -->
    <div class="modal fade" id="configDashboardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-morphism border-white-10 text-white">
                <div class="modal-header border-white-10">
                    <h5 class="modal-title fw-black"><?= __('dashboard.config_widgets') ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo url('dashboard/updateConfig'); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <p class="text-white-50 small mb-4"><?= __('dashboard.config_widgets_help') ?>
                        </p>

                        <?php foreach ($widget_config as $key => $config): ?>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox"
                                    name="widgets[<?php echo $key; ?>][is_visible]" id="widget_<?php echo $key; ?>" <?php echo $config['is_visible'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="widget_<?php echo $key; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $key)); ?>
                                </label>
                                <input type="hidden" name="widgets[<?php echo $key; ?>][sort_order]"
                                    value="<?php echo $config['sort_order']; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer border-white-10">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4"
                            data-bs-dismiss="modal"><?= __('common.cancel') ?></button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><?= __('common.save') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Dynamic Widget Rendering based on Sort Order -->
    <div class="col-12">
        <div class="row g-4" id="dashboard-widgets-container">
            <?php
            // Sort widget config by sort_order
            $sortedWidgets = $widget_config;
            uasort($sortedWidgets, function ($a, $b) {
                return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
            });

            foreach ($sortedWidgets as $key => $config):
                if (!$config['is_visible'])
                    continue;

                // Capture widget HTML
                $widgetPath = "widgets/{$key}.php";
                $widgetHTML = '';

                // Absolute path check for safety
                $fullPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . $widgetPath;

                if (file_exists($fullPath)) {
                    ob_start();
                    include $fullPath;
                    $widgetHTML = ob_get_clean();
                }

                if (!empty($widgetHTML)):
                    ?>
                    <div class="widget-wrapper col-12 <?php
                    // Dynamic column widths based on widget type
                    echo in_array($key, ['performance_chart']) ? 'col-lg-8' :
                        (in_array($key, ['resource_dist', 'sla_summary']) ? 'col-lg-4' :
                            (in_array($key, ['bi_indicators', 'insight_alerts']) ? 'col-lg-6' : 'col-12'));
                    ?>" data-widget-id="<?php echo $key; ?>">
                        <div class="widget-container h-100 position-relative">
                            <!-- Drag Handle -->
                            <div class="widget-drag-handle position-absolute top-0 start-50 translate-middle-x p-2 no-print"
                                style="cursor: grab; z-index: 10; opacity: 0.3; transition: opacity 0.3s;">
                                <span class="material-symbols-outlined fs-5">drag_indicator</span>
                            </div>
                            <?php echo $widgetHTML; ?>
                        </div>
                    </div>
                    <?php
                endif;
            endforeach;
            ?>
        </div>
    </div>
</div>

<!-- SortableJS for Drag-and-Drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Drag and Drop Implementation ---
        const container = document.getElementById('dashboard-widgets-container');
        if (container) {
            new Sortable(container, {
                animation: 150,
                handle: '.widget-drag-handle',
                ghostClass: 'glass-morphism-ghost',
                onEnd: function () {
                    const order = Array.from(container.querySelectorAll('.widget-wrapper'))
                        .map(el => el.dataset.widgetId);

                    saveWidgetOrder(order);
                }
            });
        }

        function saveWidgetOrder(order) {
            const formData = new FormData();
            order.forEach(id => formData.append('order[]', id));

            fetch('<?php echo url('dashboard/saveOrder'); ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?php echo \Core\Session::get('csrf_token'); ?>'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('<?= __('dashboard.order_saved') ?>');
                    }
                });
        }

        function showToast(message) {
            // Simple toast implementation or use bootstrap toast if available
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3 no-print';
            toast.style.zIndex = '1080';
            toast.innerHTML = `
                <div class="glass-morphism border-white-10 p-3 rounded-3 text-white shadow-lg d-flex align-items-center gap-2" style="backdrop-filter: blur(20px);">
                    <span class="material-symbols-outlined text-success">check_circle</span>
                    <span class="small fw-bold">${message}</span>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.5s ease';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        // --- 2. Chart and Stats Logic ---
        // Real data from PHP
    const dailyPerf = <?php echo json_encode($daily_perf); ?>;
    const monthlyPerf = <?php echo json_encode($monthly_perf); ?>;
    const resourceStats = <?php echo json_encode($resource_stats); ?>;
    const financialData = <?php echo json_encode($financial ?? ['history' => []]); ?>;

    const perfEl = document.getElementById('performanceChart');
    const resEl = document.getElementById('resourceChart');
    const revEl = document.getElementById('revenueHistoryChart');
    const periodSelect = document.getElementById('periodSelect');

    let performanceChart;
    let revenueChart;

    const months = [
        '<?= __('months.jan') ?>', '<?= __('months.feb') ?>', '<?= __('months.mar') ?>', 
        '<?= __('months.apr') ?>', '<?= __('months.may') ?>', '<?= __('months.jun') ?>', 
        '<?= __('months.jul') ?>', '<?= __('months.aug') ?>', '<?= __('months.sep') ?>', 
        '<?= __('months.oct') ?>', '<?= __('months.nov') ?>', '<?= __('months.dec') ?>'
    ];
    const days = [
        '<?= __('days.sun') ?>', '<?= __('days.mon') ?>', '<?= __('days.tue') ?>', 
        '<?= __('days.wed') ?>', '<?= __('days.thu') ?>', '<?= __('days.fri') ?>', '<?= __('days.sat') ?>'
    ];

    // --- Revenue Chart Initialization ---
    if (revEl) {
        const revCtx = revEl.getContext('2d');
        const revLabels = Object.keys(financialData.history).map(m => {
            const parts = m.split('-');
            const date = new Date(parts[0], parts[1] - 1, 1);
            return months[date.getMonth()] + " " + parts[0].substr(-2);
        });
        const revValues = Object.values(financialData.history);

        revenueChart = new Chart(revCtx, {
            type: 'bar',
            data: {
                labels: revLabels,
                datasets: [{
                    label: 'Ingresos Netos ($)',
                    data: revValues,
                    backgroundColor: 'rgba(212, 175, 55, 0.2)', // We can improve this with getComputedStyle if needed but keeping it consistent with the elegant gold
                    borderColor: getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-gold').trim() || '#D4AF37',
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } }
                    },
                    x: { 
                        grid: { display: false }, 
                        ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } }
                    }
                }
            }
        });
    }

    function getChartContent(period) {
            let labels = [];
            let dataset1 = []; // Tickets
            let dataset2 = []; // Usuarios
            let dataset3 = []; // Clientes

            let filtered = [];
            if (period === '7d') filtered = dailyPerf.slice(-7);
            else if (period === '30d') filtered = dailyPerf.slice(-30);
            else if (period === '3m') filtered = monthlyPerf.slice(-3);
            else filtered = monthlyPerf;

            filtered.forEach(item => {
                if (item.date) {
                    const date = new Date(item.date + 'T00:00:00');
                    if (period === '7d') {
                        labels.push(days[date.getDay()]);
                    } else {
                        labels.push(`${date.getDate()} ${months[date.getMonth()]}`);
                    }
                } else if (item.month) {
                    const parts = item.month.split('-');
                    const date = new Date(parts[0], parts[1] - 1, 1);
                    labels.push(`${months[date.getMonth()]}-${date.getFullYear().toString().substr(-2)}`);
                }

                dataset1.push(item.tickets);
                dataset2.push(item.users);
                dataset3.push(item.clients);
            });

            return { labels, dataset1, dataset2, dataset3 };
        }

        function initPerformanceChart(period) {
            if (!perfEl) return;
            const perfCtx = perfEl.getContext('2d');
            const { labels, dataset1, dataset2, dataset3 } = getChartContent(period);

            if (performanceChart) performanceChart.destroy();

            performanceChart = new Chart(perfCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Tickets',
                        data: dataset1,
                        borderColor: getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-gold').trim() || '#D4AF37',
                        backgroundColor: 'rgba(212, 175, 55, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                    }, {
                        label: 'Usuarios',
                        data: dataset2,
                        borderColor: getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-info').trim() || '#30C5FF',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                    }, {
                        label: 'Clientes',
                        data: dataset3,
                        borderColor: getComputedStyle(document.documentElement).getPropertyValue('--vzl-secondary').trim() || '#5C4D7D',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: 'rgba(255,255,255,0.7)', font: { family: 'var(--font-heading)' } } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: 'rgba(255,255,255,0.5)', stepSize: 1 }
                        },
                        x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.5)' } }
                    }
                }
            });
        }

        // Initial Chart
        if (perfEl) initPerformanceChart('7d');

        // Period Change Listener
        if (periodSelect) {
            periodSelect.addEventListener('change', function () {
                initPerformanceChart(this.value);
            });
        }

        // 2. Resource Chart (Stacked Bar grouped by Pillar and Status)
        if (resEl) {
            const resCtx = resEl.getContext('2d');

            const categories = Object.keys(resourceStats);
            const statusMap = {
                'open': { label: 'Abierto', color: getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-info').trim() },
                'in_analysis': { label: 'En Análisis', color: getComputedStyle(document.documentElement).getPropertyValue('--vzl-secondary').trim() },
                'budget_sent': { label: 'P. Enviado', color: 'rgba(212, 175, 55, 0.6)' },
                'budget_approved': { label: 'P. Aprobado', color: getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-gold').trim() },
                'budget_rejected': { label: 'P. Rechazado', color: getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-danger').trim() },
                'invoiced': { label: 'Facturado', color: getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-bg-dark').trim() },
                'payment_pending': { label: 'P. Pendiente', color: 'rgba(212,175,55,0.4)' },
                'active': { label: 'Activo', color: getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-success').trim() },
                'resolved': { label: 'Resuelto', color: 'rgba(16, 185, 129, 0.8)' },
                'closed': { label: 'Cerrado', color: '#0A0A0A' },
                'void': { label: 'Anulado', color: '#4b5563' }
            };

            const allStatusesFound = new Set();
            categories.forEach(cat => {
                Object.keys(resourceStats[cat]).forEach(st => allStatusesFound.add(st));
            });

            const datasets = [];
            Array.from(allStatusesFound).forEach(status => {
                const data = categories.map(cat => resourceStats[cat][status] || 0);
                datasets.push({
                    label: statusMap[status] ? statusMap[status].label : status,
                    data: data,
                    backgroundColor: statusMap[status] ? statusMap[status].color : 'rgba(255,255,255,0.2)',
                    borderWidth: 0,
                    borderRadius: 4
                });
            });

            new Chart(resCtx, {
                type: 'bar',
                data: {
                    labels: categories,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'bottom', labels: { color: 'rgba(255,255,255,0.7)', font: { family: 'var(--font-heading)', size: 10 } } }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } } },
                        y: { stacked: true, beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.5)', stepSize: 1 } }
                    }
                }
            });
        }

        // 3. Conversion Funnel Chart
        function initConversionFunnelChart() {
            const funnelEl = document.getElementById('conversionFunnelChart');
            if (!funnelEl) return;
            const ctx = funnelEl.getContext('2d');
            
            const convData = <?php echo json_encode($stats['conversions']); ?>;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Leads', 'Tickets', 'Budgets', 'Invoices'],
                    datasets: [{
                        label: 'Conversión %',
                        data: [100, convData.leads_to_tickets, convData.tickets_to_budgets, convData.budgets_to_invoices],
                        backgroundColor: [
                            getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-info').trim(),
                            getComputedStyle(document.documentElement).getPropertyValue('--vzl-secondary').trim(),
                            'rgba(212, 175, 55, 0.6)',
                            getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-gold').trim()
                        ],
                        borderRadius: 10,
                        barThickness: 20
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', titleFont: { size: 10 }, bodyFont: { size: 10 } }
                    },
                    scales: {
                        x: { display: false, beginAtZero: true, max: 100 },
                        y: { 
                            grid: { display: false }, 
                            ticks: { 
                                color: 'rgba(255,255,255,0.7)', 
                                font: { size: 10, weight: 'bold' } 
                            } 
                        }
                    }
                }
            });
        }
        initConversionFunnelChart();

        // 4. Search
        const searchInput = document.getElementById('adminSearchInput');
        const rows = document.querySelectorAll('.ticket-row');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase();
                rows.forEach(row => {
                    row.style.display = row.dataset.search.includes(query) ? '' : 'none';
                });
            });
        }

        // --- Sprint 4: Skeleton Screen Polish ---
        setTimeout(() => {
            document.querySelectorAll('.skeleton-loader, .skeleton-row').forEach(el => {
                el.style.transition = 'opacity 0.4s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 400);
            });
        }, 600); // Artificial delay to WOW the user with the smooth transition
    });
</script>

<style>
    /* Drag and Drop Feedback */
    .glass-morphism-ghost {
        opacity: 0.4;
        background: rgba(212, 175, 55, 0.05) !important;
        border: 2px dashed var(--vzl-color-gold) !important;
    }

    .widget-wrapper {
        transition: transform 0.2s ease;
    }

    .widget-container:hover .widget-drag-handle {
        opacity: 0.8 !important;
    }

    .widget-drag-handle:active {
        cursor: grabbing !important;
    }

    /* Dashboard Mobile Refinements */
    @media (max-width: 768px) {
        .display-6.kpi-value {
            font-size: 1.25rem !important;
        }

        .kpi-card {
            padding: 1.25rem !important;
        }

        .analytics-card {
            padding: 1.5rem !important;
        }

        h2.h5 {
            font-size: 1rem !important;
        }

        .table th,
        .table td {
            padding: 1rem !important;
            font-size: 0.8rem !important;
        }

        #adminDashboardContent {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }
    }
</style>
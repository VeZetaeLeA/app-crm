<div class="row g-4 mb-4">
    <div class="col-12">
        <h2 class="text-white fw-black mb-1">Centro de Operaciones Staff 🛠️</h2>
        <p class="text-white-50">Gestiona tus asignaciones y mantén la fluidez de los datos.</p>
    </div>

    <!-- Stats Row -->
    <div class="col-md-4">
        <div class="glass-morphism p-4 rounded-4 border-white-10 d-flex align-items-center gap-4 h-100">
            <div class="rounded-circle bg-steel text-accent d-flex align-items-center justify-content-center"
                style="width: 60px; height: 60px;">
                <span class="material-symbols-outlined fs-2">assignment_turned_in</span>
            </div>
            <div>
                <h3 class="text-white fw-black mb-0 display-6"><?php echo count($tickets); ?></h3>
                <p class="text-white-50 small mb-0 uppercase tracking-widest fw-bold">Asignaciones</p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="glass-morphism p-4 rounded-4 border-white-10 h-100">
            <div class="row align-items-center">
                <div class="col-md-6 border-end border-white-10">
                    <h6 class="text-white-50 small fw-bold uppercase tracking-widest mb-3">Distribución de Estados</h6>
                    <div style="height: 120px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6 ps-md-4">
                    <p class="text-white small mb-2 d-flex justify-content-between">
                        <span>Eficiencia Operativa</span>
                        <span class="text-accent fw-bold">94%</span>
                    </p>
                    <div class="progress bg-white-5" style="height: 6px;">
                        <div class="progress-bar bg-accent shadow-gold" style="width: 94%"></div>
                    </div>
                    <p class="text-white-50 x-small mt-3 mb-0">Basado en tiempos de resolución de la última semana.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="glass-morphism rounded-5 border-white-10 overflow-hidden mb-5">
    <div
        class="p-4 border-bottom border-white-10 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <h2 class="text-white h5 fw-black mb-0">Mi Cola de Trabajo</h2>

        <!-- Advanced Filter -->
        <div class="d-flex align-items-center gap-3">
            <div class="position-relative">
                <span
                    class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ps-3 text-white-50 fs-5">search</span>
                <input type="text" id="ticketSearch"
                    class="form-control form-control-sm ps-5 bg-white-5 border-white-10 text-white rounded-pill"
                    placeholder="Buscar ticket o cliente..." style="width: 280px;">
            </div>
            <select id="priorityFilter"
                class="form-select form-select-sm bg-white-5 border-white-10 text-white rounded-pill"
                style="width: 140px;">
                <option value="all">Prioridad: Todas</option>
                <option value="urgent">Urgente</option>
                <option value="high">Alta</option>
                <option value="normal">Normal</option>
            </select>
        </div>
    </div>

    <div class="p-4">
        <?php if (empty($tickets)): ?>
            <div class="text-center py-5">
                <span class="material-symbols-outlined display-1 text-white-10 mb-3">task_alt</span>
                <p class="text-white-50">¡Excelente! No tienes tickets pendientes por el momento.</p>
            </div>
        <?php else: ?>
            <div class="row g-4" id="ticketsList">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="col-md-6 col-xl-4 ticket-item" data-subject="<?php echo strtolower($ticket['subject']); ?>"
                        data-client="<?php echo strtolower($ticket['client_name']); ?>"
                        data-priority="<?php echo $ticket['priority']; ?>">
                        <div
                            class="glass-morphism p-4 rounded-4 border-white-10 hover-lift transition-all h-100 position-relative">
                            <div class="d-flex justify-content-between mb-3">
                                <span
                                    class="badge border border-white-10 text-white-50 x-small px-2 py-1"><?php echo $ticket['ticket_number']; ?></span>
                                <span class="badge <?php
                                echo ($ticket['priority'] == 'urgent' || $ticket['priority'] == 'high') ? 'bg-danger' : 'bg-primary';
                                ?> text-white x-small uppercase"><?php echo $ticket['priority']; ?></span>
                            </div>
                            <h5 class="text-white fw-bold mb-2"><?php echo $ticket['subject']; ?></h5>
                            <p class="text-white-50 small mb-4 line-clamp-2"><?php echo $ticket['description']; ?></p>

                            <div
                                class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top border-white-5">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-steel d-flex align-items-center justify-content-center text-accent x-small fw-bold"
                                        style="width: 24px; height: 24px;">
                                        <?php echo strtoupper(substr($ticket['client_name'], 0, 1)); ?>
                                    </div>
                                    <span class="text-white-50 x-small"><?php echo $ticket['client_name']; ?></span>
                                </div>
                                <a href="<?php echo url('ticket/detail/' . $ticket['id']); ?>"
                                    class="btn btn-primary btn-sm px-3 fw-bold small">Gestionar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Prepare data for Chart.js
$statusCounts = [];
foreach ($tickets as $t) {
    echo "<!-- Status found: " . $t['status'] . " -->"; // For debugging
    $statusCounts[$t['status']] = ($statusCounts[$t['status']] ?? 0) + 1;
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Chart.js Implementation
        const ctx = document.getElementById('statusChart').getContext('2d');
        const statusData = <?php echo json_encode($statusCounts); ?>;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map('translateStatus', array_keys($statusCounts))); ?>,
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: [
                        getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-gold').trim() || '#D4AF37',
                        getComputedStyle(document.documentElement).getPropertyValue('--vzl-primary').trim() || '#33658A',
                        getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-info').trim() || '#30C5FF',
                        getComputedStyle(document.documentElement).getPropertyValue('--vzl-secondary').trim() || '#5C4D7D',
                        getComputedStyle(document.documentElement).getPropertyValue('--vzl-color-bg-dark').trim() || '#1B1F3B'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: 'rgba(255,255,255,0.7)',
                            font: { size: 10, family: 'var(--font-heading)' },
                            boxWidth: 10
                        }
                    }
                },
                cutout: '70%'
            }
        });

        // 2. Real-time Search & Filter
        const searchInput = document.getElementById('ticketSearch');
        const priorityFilter = document.getElementById('priorityFilter');
        const tickets = document.querySelectorAll('.ticket-item');

        function filterTickets() {
            const searchTerm = searchInput.value.toLowerCase();
            const priorityTerm = priorityFilter.value;

            tickets.forEach(item => {
                const subject = item.dataset.subject;
                const client = item.dataset.client;
                const priority = item.dataset.priority;

                const matchesSearch = subject.includes(searchTerm) || client.includes(searchTerm);
                const matchesPriority = priorityTerm === 'all' || priority === priorityTerm;

                if (matchesSearch && matchesPriority) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterTickets);
        priorityFilter.addEventListener('change', filterTickets);
    });
</script>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .form-control-sm:focus {
        background-color: rgba(255, 255, 255, 0.1) !important;
    }
</style>
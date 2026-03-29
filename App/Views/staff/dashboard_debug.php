<div class="container-fluid">
    <div class="row g-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h4>🔍 Debug Info - Staff Dashboard</h4>
                <p><strong>Usuario:</strong>
                    <?php echo \Core\Auth::user()['name']; ?>
                </p>
                <p><strong>Rol:</strong>
                    <?php echo \Core\Auth::role(); ?>
                </p>
                <p><strong>Tickets asignados:</strong>
                    <?php echo count($tickets); ?>
                </p>
                <p><strong>Chart.js cargado:</strong> <span id="chartStatus">Verificando...</span></p>
            </div>
        </div>

        <div class="col-12">
            <h2 class="text-white">Centro de Operaciones Staff 🛠️</h2>
            <p class="text-white-50">Gestiona tus asignaciones y mantén la fluidez de los datos.</p>
        </div>

        <div class="col-md-4">
            <div class="glass-morphism p-4 rounded-4 border-white-10">
                <h3 class="text-white display-6">
                    <?php echo count($tickets); ?>
                </h3>
                <p class="text-white-50 small">Asignaciones</p>
            </div>
        </div>

        <div class="col-md-8">
            <div class="glass-morphism p-4 rounded-4 border-white-10">
                <h6 class="text-white-50 small mb-3">Gráfico de Prueba</h6>
                <div style="height: 200px; position: relative;">
                    <canvas id="testChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="glass-morphism p-4 rounded-4 border-white-10">
                <h4 class="text-white mb-4">Mis Tickets</h4>
                <?php if (empty($tickets)): ?>
                    <p class="text-white-50">No tienes tickets asignados.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Asunto</th>
                                    <th>Cliente</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td>
                                            <?php echo $ticket['ticket_number']; ?>
                                        </td>
                                        <td>
                                            <?php echo $ticket['subject']; ?>
                                        </td>
                                        <td>
                                            <?php echo $ticket['client_name']; ?>
                                        </td>
                                        <td><span
                                                class="badge bg-<?php echo $ticket['priority'] == 'urgent' ? 'danger' : 'primary'; ?>">
                                                <?php echo $ticket['priority']; ?>
                                            </span></td>
                                        <td>
                                            <?php echo $ticket['status']; ?>
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Verificar si Chart.js está cargado
        const chartStatus = document.getElementById('chartStatus');
        if (typeof Chart !== 'undefined') {
            chartStatus.textContent = '✅ SÍ';
            chartStatus.className = 'text-success fw-bold';

            // Crear gráfico de prueba
            try {
                const ctx = document.getElementById('testChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Abiertos', 'En Progreso', 'Cerrados'],
                        datasets: [{
                            label: 'Tickets',
                            data: [12, 19, 3],
                            backgroundColor: ['#D4AF37', '#30C5FF', '#5C4D7D']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#FFFFFF'
                                }
                            }
                        },
                        scales: {
                            y: {
                                ticks: { color: '#FFFFFF' },
                                grid: { color: 'rgba(255,255,255,0.1)' }
                            },
                            x: {
                                ticks: { color: '#FFFFFF' },
                                grid: { color: 'rgba(255,255,255,0.1)' }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error creando gráfico:', error);
                chartStatus.textContent = '❌ Error: ' + error.message;
                chartStatus.className = 'text-danger fw-bold';
            }
        } else {
            chartStatus.textContent = '❌ NO';
            chartStatus.className = 'text-danger fw-bold';
        }
    });
</script>
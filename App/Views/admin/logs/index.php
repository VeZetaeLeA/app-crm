<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <h2 class="text-white fw-bold mb-0">Logs de Auditoría</h2>
    <div class="d-flex gap-2 flex-wrap">

        <!-- Filter Year -->
        <div class="dropdown">
            <button class="btn bg-midnight text-white border-white-10 dropdown-toggle rounded-3" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                Años
            </button>
            <ul class="dropdown-menu dropdown-menu-dark p-2 bg-midnight border-white-10">
                <?php foreach ($availableYears as $y): ?>
                    <li>
                        <div class="form-check">
                            <input class="form-check-input filter-year" type="checkbox" value="<?php echo $y; ?>"
                                id="year_<?php echo $y; ?>">
                            <label class="form-check-label text-white small" for="year_<?php echo $y; ?>">
                                <?php echo $y; ?>
                            </label>
                        </div>
                    </li>
                <?php endforeach; ?>
                <li>
                    <hr class="dropdown-divider border-white-10">
                </li>
                <li>
                    <button class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1"
                        onclick="applyFilters()">
                        <span class="material-symbols-outlined fs-6">check</span> Aplicar
                    </button>
                </li>
            </ul>
        </div>

        <!-- Filter Month -->
        <div class="dropdown">
            <button class="btn bg-midnight text-white border-white-10 dropdown-toggle rounded-3" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                Meses
            </button>
            <ul class="dropdown-menu dropdown-menu-dark p-2 bg-midnight border-white-10"
                style="max-height: 250px; overflow-y: auto;">
                <?php
                $monthNames = [
                    1 => 'Enero',
                    2 => 'Febrero',
                    3 => 'Marzo',
                    4 => 'Abril',
                    5 => 'Mayo',
                    6 => 'Junio',
                    7 => 'Julio',
                    8 => 'Agosto',
                    9 => 'Septiembre',
                    10 => 'Octubre',
                    11 => 'Noviembre',
                    12 => 'Diciembre'
                ];
                foreach ($availableMonths as $num):
                    $name = $monthNames[(int) $num] ?? $num;
                    ?>
                    <li>
                        <div class="form-check">
                            <input class="form-check-input filter-month" type="checkbox" value="<?php echo $num; ?>"
                                id="month_<?php echo $num; ?>">
                            <label class="form-check-label text-white small" for="month_<?php echo $num; ?>">
                                <?php echo $name; ?>
                            </label>
                        </div>
                    </li>
                <?php endforeach; ?>
                <li class="position-sticky bottom-0 bg-midnight pt-2 mt-1 border-top border-white-10">
                    <button class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1"
                        onclick="applyFilters()">
                        <span class="material-symbols-outlined fs-6">check</span> Aplicar
                    </button>
                </li>
            </ul>
        </div>

        <select class="form-select bg-midnight border-white-10 text-white w-auto rounded-3" id="filter-level"
            onchange="applyFilters()">
            <option value="">Todos los niveles</option>
            <option value="INFO">INFO</option>
            <option value="WARN">WARN</option>
            <option value="ERROR">ERROR</option>
        </select>
        <a href="#" id="export-csv" class="btn btn-primary rounded-3 d-flex align-items-center gap-1"
            title="Descargar CSV">
            <span class="material-symbols-outlined fs-5">download</span> CSV
        </a>
        <?php if (!empty($_GET['level']) || !empty($_GET['year']) || !empty($_GET['month'])): ?>
            <button class="btn btn-outline-light rounded-3" onclick="location.href='<?php echo url('admin/log'); ?>'"
                title="Limpiar todo">
                <span class="material-symbols-outlined fs-5 align-middle">mop</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="glass-morphism rounded-4 border-white-10 overflow-hidden shadow-lg">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle">
            <thead class="bg-white-10 text-white-50 x-small uppercase tracking-widest">
                <tr>
                    <th class="ps-4">Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Detalles</th>
                    <th>Nivel</th>
                    <th>IP</th>
                    <th>Método</th>
                    <th class="pe-4">Integridad</th>
                </tr>
            </thead>
            <tbody class="text-white-50 small">
                <?php
                // Initialize verification chain (Reverse order as it's DESC)
                // Note: True chain verification requires sequential check from genesis.
                // For this view, we'll mark rows that have a signature.
                foreach ($logs as $log):
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="text-white fw-bold">
                                <?php echo date('d M, H:i:s', strtotime($log['created_at'])); ?>
                            </div>
                            <div class="x-small text-white-50">
                                <?php echo date('Y', strtotime($log['created_at'])); ?>
                            </div>
                        </td>
                        <td>
                            <div class="text-white">
                                <?php echo $log['user_email']; ?>
                            </div>
                            <div class="x-small uppercase text-accent">
                                <?php echo $log['user_role']; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-white-10 text-white border border-white-10 rounded-pill px-2 py-1">
                                <?php echo $log['action']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 200px;"
                                title='<?php echo htmlspecialchars($log['details']); ?>'>
                                <?php
                                $details = json_decode($log['details'], true);
                                if ($details && isset($details['message'])) {
                                    echo htmlspecialchars($details['message']);
                                } else {
                                    echo htmlspecialchars($log['details']);
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php
                            $levelClass = 'bg-info';
                            if ($log['level'] === 'WARN')
                                $levelClass = 'bg-warning';
                            if ($log['level'] === 'ERROR')
                                $levelClass = 'bg-danger';
                            ?>
                            <span
                                class="badge <?php echo $levelClass; ?> bg-opacity-10 <?php echo str_replace('bg-', 'text-', $levelClass); ?> border <?php echo str_replace('bg-', 'border-', $levelClass); ?> border-opacity-25 rounded-pill px-2">
                                <?php echo $log['level']; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $log['ip_address']; ?>
                        </td>
                        <td>
                            <span class="x-small fw-bold px-2 py-1 bg-deep-black rounded border border-white-10">
                                <?php echo $log['request_method']; ?>
                            </span>
                        </td>
                        <td class="pe-4">
                            <?php if (!empty($log['signature_hash'])): ?>
                                <span class="material-symbols-outlined text-success fs-5"
                                    title="Firmado Criptográficamente (Zero Trust)">shield_lock</span>
                            <?php else: ?>
                                <span class="material-symbols-outlined text-white-10 fs-5"
                                    title="Log sin firma (Legacy/Externo)">shield_question</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function applyFilters() {
        const url = new URL(window.location.href);
        url.search = ''; // Clear params

        const level = document.getElementById('filter-level').value;
        if (level) url.searchParams.set('level', level);

        document.querySelectorAll('.filter-year:checked').forEach(cb => {
            url.searchParams.append('year[]', cb.value);
        });

        document.querySelectorAll('.filter-month:checked').forEach(cb => {
            url.searchParams.append('month[]', cb.value);
        });

        window.location.href = url.toString();
    }

    // Update export CSV link dynamically
    function updateExportLink() {
        const url = new URL(window.location.href);
        const exportUrl = new URL("<?php echo url('admin/log/exportCsv'); ?>");

        url.searchParams.forEach((value, key) => {
            exportUrl.searchParams.append(key, value);
        });

        document.getElementById('export-csv').href = exportUrl.toString();
    }

    // Set initial values from URL on load
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('level')) {
            document.getElementById('filter-level').value = urlParams.get('level');
        }

        const years = urlParams.getAll('year[]');
        years.forEach(y => {
            const cb = document.getElementById('year_' + y);
            if (cb) cb.checked = true;
        });

        const months = urlParams.getAll('month[]');
        months.forEach(m => {
            const cb = document.getElementById('month_' + m);
            if (cb) cb.checked = true;
        });

        updateExportLink();
    });

    // Prevent dropdown closing when clicking checkboxes
    document.querySelectorAll('.dropdown-menu .form-check').forEach(item => {
        item.addEventListener('click', e => {
            e.stopPropagation();
        });
    });
</script>
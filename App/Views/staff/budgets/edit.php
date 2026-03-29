<?php $taxRate = \Core\Config::get('TAX_RATE', 16.00); ?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="<?php echo url('budget/show/' . $budget['id']); ?>"
                                class="text-primary text-decoration-none small fw-bold">Presupuesto #
                                <?php echo $budget['budget_number']; ?>
                            </a></li>
                        <li class="breadcrumb-item active text-white-50 small" aria-current="page">Nueva Versión (v<?php echo $budget['version'] + 1; ?>)
                        </li>
                    </ol>
                </nav>
                <h2 class="text-white fw-black m-0">Editar <span class="text-primary">Propuesta</span></h2>
            </div>
            <div class="text-end">
                <span class="text-white-50 x-small uppercase tracking-widest d-block">Cliente</span>
                <span class="text-white fw-bold">
                    <?php echo $budget['client_name']; ?>
                </span>
            </div>
        </div>

        <form action="<?php echo url('budget/updateVersion'); ?>" method="POST" id="budget-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="parent_id" value="<?php echo $budget['id']; ?>">
            <input type="hidden" name="ticket_id" value="<?php echo $budget['ticket_id']; ?>">

            <div class="row g-4">
                <!-- Main Details -->
                <div class="col-md-8">
                    <div class="glass-morphism p-4 rounded-5 border-white-10 mb-4">
                        <div class="mb-4">
                            <label class="text-white-50 small mb-2 uppercase fw-bold tracking-widest">Título de la
                                Propuesta</label>
                            <input type="text" name="title" class="form-control bg-steel border-white-10 text-white p-3"
                                value="<?php echo $budget['title']; ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="text-white-50 small mb-2 uppercase fw-bold tracking-widest">Servicio
                                Solicitado (Pilar - Servicio)</label>
                            <input type="text" name="service_reference"
                                class="form-control bg-steel border-white-10 text-white p-3"
                                value="<?php echo $budget['service_reference']; ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="text-white-50 small mb-2 uppercase fw-bold tracking-widest">Alcance del
                                Proyecto (Scope)</label>
                            <textarea name="scope" class="form-control bg-steel border-white-10 text-white p-3" rows="10"
                                placeholder="Describe detalladamente los entregables y fases..." required><?php echo $budget['scope']; ?></textarea>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="glass-morphism rounded-5 border-white-10 overflow-hidden">
                        <div class="p-3 bg-white-5 border-bottom border-white-10 px-4">
                            <h5 class="text-white h6 mb-0 fw-bold uppercase tracking-widest">Desglose de Costos</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-dark mb-0 align-middle" id="items-table">
                                <thead class="bg-deep-black">
                                    <tr class="x-small uppercase text-white-50">
                                        <th class="p-4 border-0" style="width: 50%;">Descripción</th>
                                        <th class="p-4 border-0">Cant.</th>
                                        <th class="p-4 border-0">Precio Unit.</th>
                                        <th class="p-4 border-0">Total</th>
                                        <th class="p-4 border-0"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($items as $index => $item): ?>
                                    <tr>
                                        <td class="p-3 border-white-5">
                                            <input type="text" name="items[<?php echo $index; ?>][description]"
                                                class="form-control form-control-sm bg-white-5 border-white-10 text-white"
                                                value="<?php echo $item['description']; ?>" required>
                                        </td>
                                        <td class="p-3 border-white-5">
                                            <input type="number" name="items[<?php echo $index; ?>][quantity]"
                                                class="form-control form-control-sm bg-deep-black border-white-10 text-white qty-input"
                                                style="padding: 0.5rem; height: auto; min-width: 80px;" 
                                                value="<?php echo $item['quantity']; ?>"
                                                step="0.01" required>
                                        </td>
                                        <td class="p-3 border-white-5">
                                            <input type="number" name="items[<?php echo $index; ?>][unit_price]"
                                                class="form-control form-control-sm bg-deep-black border-white-10 text-white price-input"
                                                style="padding: 0.5rem; height: auto; min-width: 100px;"
                                                value="<?php echo $item['unit_price']; ?>" step="0.01" required>
                                        </td>
                                        <td class="p-3 border-white-5 text-white fw-bold item-total">$
                                            <?php echo number_format($item['total'], 2); ?>
                                        </td>
                                        <td class="p-3 border-white-5 text-center">
                                            <button type="button"
                                                class="btn btn-link btn-sm text-danger remove-item p-0"><span
                                                    class="material-symbols-outlined fs-5">delete</span></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 bg-white-5 border-top border-white-10 text-end px-4">
                            <button type="button" class="btn btn-primary btn-sm px-4 shadow-gold" id="add-item">
                                <span class="material-symbols-outlined fs-5 align-middle me-1">add_circle</span> Añadir
                                Ítem
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Summary -->
                <div class="col-md-4">
                    <div class="glass-morphism p-4 rounded-5 border-white-10 sticky-top" style="top: 100px;">
                        <h5 class="text-white fw-bold mb-4 uppercase tracking-widest small">Resumen Financiero</h5>

                        <div class="mb-4">
                            <label class="text-white-50 small mb-2 uppercase fw-bold tracking-widest">Tiempo Estimado
                                (Semanas)</label>
                            <input type="number" name="timeline_weeks"
                                class="form-control bg-steel border-white-10 text-white" value="<?php echo $budget['timeline_weeks']; ?>" required>
                        </div>

                        <div class="space-y-3 mb-4 border-top border-white-10 pt-4">
                            <div class="d-flex justify-content-between text-white-50 small">
                                <span>Subtotal:</span>
                                <span id="summary-subtotal">$
                                    <?php echo number_format($budget['subtotal'], 2); ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between text-white-50 small">
                                <span>IVA (<?php echo $taxRate; ?>%):</span>
                                <span id="summary-tax">$
                                    <?php echo number_format($budget['tax_amount'], 2); ?>
                                </span>
                            </div>
                            <hr class="border-white-10">
                            <div class="d-flex justify-content-between align-items-center text-white h4 fw-black">
                                <span class="text-nowrap">Total:</span>
                                <span id="summary-total"
                                    class="text-primary text-nowrap">$<?php echo number_format($budget['total'], 2); ?></span>
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-primary w-100 py-3 fw-black uppercase tracking-widest shadow-gold">
                            Generar Versión v<?php echo $budget['version'] + 1; ?> <span class="material-symbols-outlined ms-2 align-middle">auto_mode</span>
                        </button>
                        <p class="text-center text-white-50 x-small mt-3 mb-0">Esta acción reemplazará la versión actual para el cliente.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        let itemIndex = <?php echo count($items); ?>;
        const taxRate = <?php echo floatval($taxRate) / 100; ?>;

        function calculateTotals() {
            let subtotal = 0;
            const rows = document.querySelectorAll('#items-table tbody tr');

            rows.forEach(tr => {
                const qtyInput = tr.querySelector('.qty-input');
                const priceInput = tr.querySelector('.price-input');
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const total = qty * price;

                const totalDisplay = tr.querySelector('.item-total');
                if (totalDisplay) {
                    totalDisplay.innerText = '$' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                subtotal += total;
            });

            const tax = subtotal * taxRate;
            const total = subtotal + tax;

            const subtotalEl = document.getElementById('summary-subtotal');
            const taxEl = document.getElementById('summary-tax');
            const totalEl = document.getElementById('summary-total');

            if (subtotalEl) subtotalEl.innerText = '$' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (taxEl) taxEl.innerText = '$' + tax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (totalEl) totalEl.innerText = '$' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function attachEvents() {
            document.querySelectorAll('.qty-input, .price-input').forEach(input => {
                input.removeEventListener('input', calculateTotals);
                input.addEventListener('input', calculateTotals);
            });

            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.onclick = null;
                btn.onclick = function () {
                    const rows = document.querySelectorAll('#items-table tbody tr');
                    if (rows.length > 1) {
                        this.closest('tr').remove();
                        calculateTotals();
                    } else {
                        alert('Debe haber al menos un ítem en el presupuesto.');
                    }
                };
            });
        }

        function init() {
            const addItemBtn = document.getElementById('add-item');
            const itemsTable = document.querySelector('#items-table tbody');

            if (addItemBtn && itemsTable) {
                addItemBtn.addEventListener('click', function () {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td class="p-3 border-white-5">
                        <input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm bg-white-5 border-white-10 text-white" required>
                    </td>
                    <td class="p-3 border-white-5">
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm bg-deep-black border-white-10 text-white qty-input" style="padding: 0.5rem; height: auto; min-width: 80px;" value="1" step="0.01" required>
                    </td>
                    <td class="p-3 border-white-5">
                        <input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm bg-deep-black border-white-10 text-white price-input" style="padding: 0.5rem; height: auto; min-width: 100px;" value="0.00" step="0.01" required>
                    </td>
                    <td class="p-3 border-white-5 text-white fw-bold item-total">$0.00</td>
                    <td class="p-3 border-white-5 text-center">
                        <button type="button" class="btn btn-link btn-sm text-danger remove-item p-0">
                            <span class="material-symbols-outlined fs-5">delete</span>
                        </button>
                    </td>
                `;
                    itemsTable.appendChild(tr);
                    itemIndex++;
                    attachEvents();
                });
            }

            attachEvents();
            calculateTotals();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>

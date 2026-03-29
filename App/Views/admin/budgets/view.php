<div class="row justify-content-center">
    <div class="col-12">
        <!-- Budget Status Header -->
        <div class="d-flex align-items-center justify-content-between mb-5">
            <div>
                <span class="badge border border-white-10 text-white-50 px-3 py-2 uppercase x-small mb-3">
                    <?php echo $budget['budget_number']; ?>
                </span>
                <h2 class="text-white fw-black m-0">Propuesta de <span class="text-primary">Servicio</span></h2>
            </div>
            <div class="text-end">
                <p class="text-white-50 small mb-1 uppercase tracking-widest fw-bold">Estado Actual</p>
                <span
                    class="badge bg-<?php echo ($budget['status'] == 'approved' ? 'success' : ($budget['status'] == 'rejected' ? 'danger' : 'primary')); ?> px-4 py-2 uppercase fs-6 fw-black">
                    <?php echo translateStatus($budget['status']); ?>
                </span>
                <?php if (!\Core\Auth::isClient() && $budget['status'] == 'sent'): ?>
                    <div class="mt-3">
                        <a href="<?= url('budget/edit/' . $budget['id']) ?>" 
                           class="btn btn-outline-primary btn-sm px-3 uppercase fw-bold tracking-widest">
                            <span class="material-symbols-outlined align-middle me-1 fs-6">edit</span> Editar Propuesta
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <!-- Document Body -->
            <div class="col-lg-8">
                <div class="glass-morphism p-5 rounded-5 border-white-10 bg-white-5 mb-4 shadow-2xl printable-doc">
                    <!-- Logo & Header -->
                    <div
                        class="d-flex justify-content-between align-items-start mb-5 pb-5 border-bottom border-white-10">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?php echo url('assets/images/logo.png'); ?>" alt="Logo"
                                class="rounded-circle shadow-gold"
                                style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h2 class="text-white h5 mb-0 fw-black"><?php echo getenv('COMPANY_NAME'); ?></h2>
                                <p class="text-white-50 x-small mb-0 uppercase tracking-widest">
                                    <?php echo getenv('COMPANY_SLOGAN'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-end x-small text-white-50">
                            <p class="mb-1">Fecha Emisión:
                                <?php echo date('d/m/Y', strtotime($budget['created_at'])); ?>
                            </p>
                            <p class="mb-0">Válido por:
                                <?php echo $budget['valid_days']; ?> días
                            </p>
                        </div>
                    </div>

                    <!-- Service Reference -->
                    <div
                        class="mb-5 bg-primary bg-opacity-10 p-4 rounded-4 border border-primary border-opacity-20 d-flex align-items-center gap-3">
                        <div class="bg-primary p-3 rounded-circle text-white shadow-gold">
                            <span class="material-symbols-outlined fs-4 align-middle">verified</span>
                        </div>
                        <div>
                            <p class="text-primary x-small fw-bold uppercase tracking-widest mb-1">Servicio Solicitado:
                            </p>
                            <h4 class="text-white h5 fw-black mb-0"><?php echo $budget['service_reference'] ?: 'N/D'; ?>
                            </h4>
                        </div>
                    </div>

                    <!-- Client Info -->
                    <div class="row mb-5 align-items-end">
                        <div class="col-md-6">
                            <p class="text-primary x-small fw-bold uppercase tracking-widest mb-3">Preparado para:</p>
                            <h4 class="text-white h6 fw-bold mb-1">
                                <?php echo $budget['client_name']; ?>
                            </h4>
                            <p class="text-white-50 small mb-1">
                                <?php echo $budget['client_company']; ?>
                            </p>
                            <div class="d-flex align-items-center gap-3">
                                <p class="text-white-50 small mb-0">
                                    <span class="material-symbols-outlined fs-6 align-middle me-1">mail</span>
                                    <?php echo $budget['client_email']; ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="text-primary x-small fw-bold uppercase tracking-widest mb-3">Ticket Referencia:
                            </p>
                            <h4 class="text-white h5 fw-black mb-0">#<?php echo $budget['ticket_number']; ?></h4>
                        </div>
                    </div>

                    <!-- Scope -->
                    <div class="mb-5 bg-white-5 p-4 rounded-4 border border-white-5">
                        <h5
                            class="text-white small fw-bold uppercase tracking-widest mb-3 border-bottom border-white-5 pb-2">
                            <span class="material-symbols-outlined fs-5 align-middle me-2">description</span>
                            Alcance de la Propuesta
                        </h5>
                        <p class="text-white-50 small lh-lg mb-0 text-justify">
                            <?php echo nl2br($budget['scope']); ?>
                        </p>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-5">
                        <h5
                            class="text-white small fw-bold uppercase tracking-widest mb-3 border-bottom border-white-5 pb-2">
                            Desglose de Inversión</h5>
                        <table class="table table-dark table-borderless align-middle mt-3">
                            <thead>
                                <tr class="x-small text-white-50 uppercase border-bottom border-white-10">
                                    <th class="p-3">Descripción</th>
                                    <th class="p-3 text-center">Cant.</th>
                                    <th class="p-3 text-end">Precio Unit.</th>
                                    <th class="p-3 text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="p-3">
                                            <p class="text-white small mb-0 fw-bold">
                                                <?php echo $item['description']; ?>
                                            </p>
                                        </td>
                                        <td class="p-3 text-center text-white-50 small">
                                            <?php echo number_format($item['quantity'], 2); ?>
                                        </td>
                                        <td class="p-3 text-end text-white-50 small">$
                                            <?php echo number_format($item['unit_price'], 2); ?>
                                        </td>
                                        <td class="p-3 text-end text-white small fw-bold">$
                                            <?php echo number_format($item['total'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-6 col-lg-5">
                            <div class="glass-morphism p-4 rounded-4 border-white-10">
                                <div class="d-flex justify-content-between text-white-50 small mb-2">
                                    <span>Subtotal:</span>
                                    <span class="fw-bold">$<?php echo number_format($budget['subtotal'], 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between text-white-50 small mb-3">
                                    <span>IVA (<?php echo number_format($budget['tax_rate'], 0); ?>%):</span>
                                    <span class="fw-bold">$<?php echo number_format($budget['tax_amount'], 2); ?></span>
                                </div>
                                <div
                                    class="d-flex justify-content-between align-items-center text-white border-top border-white-10 pt-3 h4 fw-black">
                                    <span class="text-nowrap">TOTAL:</span>
                                    <span
                                        class="text-primary text-nowrap">$<?php echo number_format($budget['total'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Actions -->
            <div class="col-lg-4 no-print">
                <div class="sticky-top" style="top: 100px;">
                    <?php if ($budget['status'] == 'sent'): ?>
                        <div
                            class="glass-morphism p-4 rounded-5 border-primary border-opacity-25 bg-white-5 shadow-gold mb-4">
                            <h5 class="text-white fw-bold mb-4 uppercase tracking-widest small">Decisión del Cliente
                            </h5>
                            <p class="text-white-50 small mb-4">Por favor revisa detenidamente los términos y el
                                presupuesto
                                antes de proceder.</p>

                            <form action="<?php echo url('budget/decision'); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="budget_id" value="<?php echo $budget['id']; ?>">
                                <div class="d-flex flex-column gap-3">
                                    <button type="submit" name="decision" value="approved"
                                        class="btn btn-primary btn-lg py-3 fw-black uppercase tracking-widest shadow-gold">
                                        <span class="material-symbols-outlined align-middle me-2">check_circle</span>
                                        Aprobar Propuesta
                                    </button>
                                    <button type="submit" name="decision" value="rejected"
                                        class="btn btn-outline-danger btn-lg py-3 fw-bold uppercase tracking-widest">
                                        <span class="material-symbols-outlined align-middle me-2">cancel</span> Rechazar
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php elseif ($budget['status'] == 'approved'): ?>
                        <div
                            class="glass-morphism p-4 rounded-5 border-success border-opacity-25 bg-success bg-opacity-10 text-center mb-4">
                            <div class="rounded-circle bg-success text-white mx-auto d-flex align-items-center justify-content-center mb-3"
                                style="width: 50px; height: 50px;">
                                <span class="material-symbols-outlined">check</span>
                            </div>
                            <h5 class="text-white fw-bold mb-2">Propuesta Aprobada</h5>
                            <p class="text-success small mb-0">Nuestro equipo está generando la factura correspondiente
                                para
                                iniciar el proyecto.</p>
                        </div>
                    <?php endif; ?>

                    <?php if (count($history) > 1): ?>
                        <div class="glass-morphism p-4 rounded-5 border-white-10 mb-4">
                            <h5 class="text-white fw-bold mb-4 uppercase tracking-widest small">Historial de Versiones</h5>
                            <div class="timeline-small">
                                <?php foreach ($history as $h): ?>
                                    <div class="d-flex gap-3 mb-3 position-relative">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="rounded-circle <?= ($h['id'] == $budget['id'] ? 'bg-primary shadow-gold' : 'bg-white-10') ?>" 
                                                 style="width: 12px; height: 12px;"></div>
                                            <div class="flex-grow-1 border-start border-white-10 my-1"></div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <a href="<?= url('budget/show/' . $h['id']) ?>" 
                                                   class="text-decoration-none <?= ($h['id'] == $budget['id'] ? 'text-primary' : 'text-white') ?> fw-bold small">
                                                    v<?= $h['version'] ?> - <?= $h['budget_number'] ?>
                                                </a>
                                                <span class="badge border border-white-10 text-white-50 x-small fw-normal">
                                                    <?= date('d/m/y', strtotime($h['created_at'])) ?>
                                                </span>
                                            </div>
                                            <span class="text-white-50 x-small uppercase"><?= translateStatus($h['status']) ?></span>
                                            <?php if ($h['id'] == $budget['id']): ?>
                                                <span class="x-small text-primary fw-bold ms-2">(Actual)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="glass-morphism p-4 rounded-5 border-white-10">
                        <h5 class="text-white fw-bold mb-4 uppercase tracking-widest small">Información Adicional
                        </h5>

                        <?php if (!\Core\Auth::isClient() && $budget['status'] == 'approved'): ?>
                            <div class="mb-4">
                                <a href="<?php echo url('invoice/createFromBudget/' . $budget['id']); ?>"
                                    class="btn btn-primary w-100 py-3 fw-black uppercase tracking-widest shadow-gold">
                                    <span class="material-symbols-outlined align-middle me-2">receipt_long</span>
                                    Generar
                                    Factura
                                </a>
                                <p class="text-center text-white-50 x-small mt-2">Esta acción permitirá al cliente
                                    realizar
                                    el pago.</p>
                            </div>
                        <?php endif; ?>

                        <ul class="list-unstyled text-white-50 small">
                            <li class="mb-3 d-flex gap-3">
                                <span class="material-symbols-outlined text-primary fs-5">schedule</span>
                                <span>Tiempo de entrega estimado: <strong>
                                        <?php echo $budget['timeline_weeks']; ?> semanas
                                    </strong>.</span>
                            </li>
                            <li class="mb-3 d-flex gap-3">
                                <span class="material-symbols-outlined text-primary fs-5">account_balance_wallet</span>
                                <span>Moneda: <strong>
                                        <?php echo $budget['currency']; ?>
                                    </strong>.</span>
                            </li>
                        </ul>
                        <a href="<?= url('budget/exportPdf/' . $budget['id']) ?>"
                            class="btn btn-outline-light w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined fs-5">picture_as_pdf</span> Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
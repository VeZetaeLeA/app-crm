<div class="row justify-content-center">
    <div class="col-lg-9">
        <!-- Status Header -->
        <div class="d-flex align-items-center justify-content-between mb-5">
            <div>
                <span class="badge border border-white-10 text-white-50 px-3 py-2 uppercase x-small mb-3">Factura No.
                    <?php echo $invoice['invoice_number']; ?>
                </span>
                <h2 class="text-white fw-black m-0">Estado de <span class="text-primary">Cuenta</span></h2>
            </div>
            <div class="text-end">
                <p class="text-white-50 small mb-1 uppercase tracking-widest fw-bold">Estado</p>
                <?php
                $invStatus = \App\Domain\Invoice\InvoiceStatus::fromString($invoice['status']);
                ?>
                <span class="badge <?php echo $invStatus->getBadgeClass(); ?> px-4 py-2 uppercase fs-6 fw-black">
                    <?php echo $invStatus->getLabel(); ?>
                </span>
            </div>
        </div>

        <div
            class="glass-morphism p-5 rounded-5 border-white-10 shadow-2xl bg-white-5 mb-5 position-relative overflow-hidden printable-doc">

            <!-- Invoice Content -->
            <div class="row mb-5 pb-5 border-bottom border-white-10">
                <div class="col-6">
                    <img src="<?php echo url('assets/images/logo.png'); ?>" alt="Logo"
                        class="rounded-circle shadow-gold mb-3" style="width: 60px; height: 60px; object-fit: cover;">
                    <h2 class="text-white h5 mb-1 fw-black"><?php echo \Core\Config::get('business.company_name'); ?></h2>
                    <p class="text-white-50 small mb-0">
                        <?php echo \Core\Config::get('business.company_address'); ?><br><?php echo \Core\Config::get('business.company_mail'); ?>
                    </p>
                </div>
                <div class="col-6 text-end">
                    <h4 class="text-primary fw-black mb-1">
                        <span class="material-symbols-outlined align-middle me-2">receipt</span>FACTURA
                    </h4>
                    <p class="text-white-50 small mb-1">Ref: #
                        <?php echo $invoice['budget_number']; ?>
                    </p>
                    <p class="text-white-50 small mb-0">Emisión:
                        <?php echo date('d/m/Y', strtotime($invoice['issue_date'])); ?>
                    </p>
                    <p class="text-white small fw-bold">Vence:
                        <?php echo date('d/m/Y', strtotime($invoice['due_date'])); ?>
                    </p>
                </div>
            </div>

            <div class="row mb-5 align-items-end">
                <div class="col-md-6 border-end border-white-10">
                    <p class="text-primary x-small fw-bold uppercase tracking-widest mb-3">Cliente:</p>
                    <h4 class="text-white h6 fw-bold mb-1">
                        <?php echo $invoice['client_name']; ?>
                    </h4>
                    <p class="text-white-50 small mb-1">
                        <?php echo $invoice['client_company']; ?>
                    </p>
                    <div class="d-flex align-items-center gap-3">
                        <p class="text-white-50 small mb-0">
                            <span class="material-symbols-outlined fs-6 align-middle me-1">mail</span>
                            <?php echo $invoice['client_email']; ?>
                        </p>
                        <p class="text-white-50 small mb-0">
                            <span class="material-symbols-outlined fs-6 align-middle me-1">call</span>
                            <?php echo $invoice['client_phone'] ?: 'N/D'; ?>
                        </p>
                    </div>
                </div>
                <div class="col-md-6 ps-md-4">
                    <p class="text-primary x-small fw-bold uppercase tracking-widest mb-3">Servicio Solicitado:</p>
                    <h5 class="text-white h6 fw-bold mb-0 text-primary italic">
                        <?php echo $invoice['service_reference'] ?: 'N/D'; ?>
                    </h5>
                </div>
            </div>

            <div class="table-responsive mb-5">
                <table class="table table-dark table-borderless">
                    <thead>
                        <tr class="x-small text-white-50 uppercase border-bottom border-white-10">
                            <th class="p-3">Concepto / Servicio</th>
                            <th class="p-3 text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-3">
                                <p class="text-white small mb-0 fw-bold">Implementación de Solución de Datos</p>
                                <p class="text-white-50 x-small mb-0">Relacionado al Presupuesto
                                    <?php echo $invoice['budget_number']; ?>
                                </p>
                            </td>
                            <td class="p-3 text-end text-white fw-bold">$
                                <?php echo number_format($invoice['subtotal'], 2); ?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-top border-white-10">
                            <td class="p-3 text-end text-white-50 small">Subtotal:</td>
                            <td class="p-3 text-end text-white small">$
                                <?php echo number_format($invoice['subtotal'], 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="p-3 text-end text-white-50 small">IVA
                                (<?php echo number_format(($invoice['tax_amount'] / $invoice['subtotal']) * 100, 0); ?>%):
                            </td>
                            <td class="p-3 text-end text-white small">$
                                <?php echo number_format($invoice['tax_amount'], 2); ?>
                            </td>
                        </tr>
                        <tr class="h3 fw-black">
                            <td class="p-3 text-end text-white">TOTAL FACTURA:</td>
                            <td class="p-3 text-end text-primary">$
                                <?php echo number_format($invoice['total'], 2); ?>
                            </td>
                        </tr>
                        <?php if ($invoice['paid_amount'] > 0): ?>
                            <tr class="h5 fw-bold text-success">
                                <td class="p-3 text-end">MONTO PAGADO:</td>
                                <td class="p-3 text-end">-$<?php echo number_format($invoice['paid_amount'], 2); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php
                        $pending_amount = $invoice['total'] - $invoice['paid_amount'];
                        if ($pending_amount > 0 && $invoice['status'] != 'paid'):
                            ?>
                            <tr class="h4 fw-black text-danger">
                                <td class="p-3 text-end">MONTO PENDIENTE:</td>
                                <td class="p-3 text-end">$<?php echo number_format($pending_amount, 2); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>
            </div>

            <!-- Notes -->
            <div class="bg-white-5 p-4 rounded-4 border-white-10 mt-5">
                <h6 class="text-white x-small fw-bold uppercase tracking-widest mb-3">Información de Sistema</h6>
                <p class="text-white-50 small mb-0">
                    ID Interno: <?php echo $invoice['id']; ?><br>
                    Generado el: <?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?><br>
                    Presupuesto Origen: <a href="<?php echo url('budget/show/' . $invoice['budget_id']); ?>"
                        class="text-primary fw-bold">#<?php echo $invoice['budget_number']; ?></a>
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="row g-4 no-print">
            <div class="col-md-6">
                <?php if ($invoice['status'] == 'processing'): ?>
                    <div class="glass-morphism p-4 rounded-5 border-success border-opacity-25 shadow-gold">
                        <h5 class="text-white fw-bold mb-3 small uppercase">Verificación de Pago</h5>
                        <p class="text-white-50 small mb-4">El cliente ha subido un comprobante. Por favor, revísalo para
                            confirmar la activación del servicio.</p>
                        <div class="d-flex flex-column gap-2">
                            <?php if (isset($receipt) && $receipt): ?>
                                <div class="bg-deep-black bg-opacity-30 p-3 rounded-4 mb-2 text-center border border-white-10">
                                    <span class="d-block text-white-50 x-small fw-bold uppercase tracking-widest mb-1">Monto
                                        Reportado</span>
                                    <span
                                        class="d-block text-success h4 fw-black mb-0">$<?php echo number_format($receipt['amount'], 2); ?></span>
                                </div>
                                <a href="<?php echo url('public/' . $receipt['filepath']); ?>" target="_blank"
                                    class="btn btn-outline-light w-100 py-3 fw-bold uppercase d-flex align-items-center justify-content-center gap-2">
                                    <span class="material-symbols-outlined">visibility</span>
                                    Ver Comprobante Adjunto
                                </a>
                                <a href="<?php echo url('invoice/confirm/' . $invoice['id']); ?>"
                                    class="btn btn-success w-100 py-3 fw-black uppercase tracking-widest shadow-gold d-flex align-items-center justify-content-center gap-2">
                                    <span class="material-symbols-outlined">verified_user</span>
                                    Validar y Registrar Pago
                                </a>
                            <?php else: ?>
                                <p class="text-danger small italic">Error: No se encontró el archivo del comprobante.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($invoice['status'] == 'unpaid' || $invoice['status'] == 'partial'): ?>
                    <div
                        class="glass-morphism p-4 rounded-5 border-warning border-opacity-25 bg-warning bg-opacity-10 text-center">
                        <span class="material-symbols-outlined display-6 mb-2 text-warning">hourglass_empty</span>
                        <h5 class="text-white fw-bold mb-1 small uppercase">Esperando Pago</h5>
                        <p class="text-white-50 small mb-0">El cliente aún tiene un monto pendiente por pagar en esta
                            factura.</p>
                    </div>
                <?php elseif ($invoice['status'] == 'paid'): ?>
                    <div
                        class="glass-morphism p-4 rounded-5 border-success border-opacity-25 bg-success bg-opacity-10 text-center">
                        <span class="material-symbols-outlined display-6 mb-2 text-success">task_alt</span>
                        <h5 class="text-white fw-bold mb-1 small uppercase text-success">Pago Completado</h5>
                        <p class="text-white-50 small mb-0 text-success">Esta factura ha sido pagada y el servicio está
                            activo.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <a href="<?= url('invoice/exportPdf/' . $invoice['id']) ?>"
                    class="btn btn-outline-white w-100 py-4 h-100 d-flex align-items-center justify-content-center gap-3 glass-morphism rounded-5">
                    <span class="material-symbols-outlined fs-2">cloud_download</span>
                    <span class="fw-bold uppercase tracking-widest">Descargar PDF</span>
                </a>
            </div>

            <!-- Admin Financial Ops -->
            <div class="col-md-12 mt-4">
                <div class="glass-morphism p-4 rounded-5 border-white-10 d-flex flex-wrap gap-3 align-items-center justify-content-between no-print">
                    <div>
                        <h6 class="text-white small mb-1 uppercase fw-bold">Gestión Financiera</h6>
                        <p class="text-white-50 x-small mb-0">Operaciones administrativas para esta factura.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if ($invoice['status'] != 'void'): ?>
                            <button type="button" class="btn btn-outline-danger btn-sm px-3 uppercase fw-bold" 
                                    data-bs-toggle="modal" data-bs-target="#voidModal">
                                <span class="material-symbols-outlined fs-6 align-middle">block</span> Anular
                            </button>
                        <?php endif; ?>

                        <?php if ($invoice['paid_amount'] > 0): ?>
                            <button type="button" class="btn btn-outline-warning btn-sm px-3 uppercase fw-bold" 
                                    data-bs-toggle="modal" data-bs-target="#refundModal">
                                <span class="material-symbols-outlined fs-6 align-middle">undo</span> Reembolsar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Void Modal -->
<div class="modal fade" id="voidModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-deep-black border-white-10 text-white rounded-5 border-0 shadow-lg">
            <form action="<?= url('invoice/void/' . $invoice['id']) ?>" method="POST">
                <div class="modal-header border-white-10">
                    <h5 class="modal-title fw-black uppercase text-danger">Anular Factura</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-white-50 small mb-4">¿Estás seguro de que deseas anular esta factura? Esta acción es irreversible y afectará los reportes financieros.</p>
                    <div class="mb-3">
                        <label class="form-label x-small uppercase fw-bold text-primary">Motivo de Anulación</label>
                        <textarea name="reason" class="form-control bg-white-5 border-white-10 text-white" rows="3" required placeholder="Eje: Error en montos, el cliente desistió..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-white-10">
                    <button type="button" class="btn btn-outline-white uppercase fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger uppercase fw-black px-4">Confirmar Anulación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-deep-black border-white-10 text-white rounded-5 border-0 shadow-lg">
            <form action="<?= url('invoice/refund/' . $invoice['id']) ?>" method="POST">
                <div class="modal-header border-white-10">
                    <h5 class="modal-title fw-black uppercase text-warning">Emitir Reembolso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-white-50 small mb-4">Se registrará un reembolso para el cliente. Esto se restará de los ingresos netos percibidos.</p>
                    <div class="mb-3 text-center bg-white-5 p-3 rounded-4 border border-white-10">
                        <span class="d-block text-white-50 x-small uppercase fw-bold mb-1">Máximo Reembolsable</span>
                        <span class="d-block text-warning h4 fw-black mb-0">$<?php echo number_format($invoice['paid_amount'], 2); ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label x-small uppercase fw-bold text-primary">Monto a Reembolsar ($)</label>
                        <input type="number" name="amount" step="0.01" max="<?php echo $invoice['paid_amount']; ?>" min="0.01" 
                               class="form-control bg-white-5 border-white-10 text-white" required value="<?php echo $invoice['paid_amount']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label x-small uppercase fw-bold text-primary">Motivo / Notas</label>
                        <textarea name="reason" class="form-control bg-white-5 border-white-10 text-white" rows="2" placeholder="Notas internas..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-white-10">
                    <button type="button" class="btn btn-outline-white uppercase fw-bold" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning uppercase fw-black px-4">Procesar Reembolso</button>
                </div>
            </form>
        </div>
    </div>
</div>
        </div>
    </div>
</div>
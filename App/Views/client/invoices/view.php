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
                    <h3 class="text-white h5 mb-1 fw-bold"><?php echo getenv('COMPANY_NAME'); ?></h3>
                    <p class="text-white-50 small mb-0">
                        <?php echo getenv('COMPANY_ADDRESS'); ?><br><?php echo getenv('COMPANY_MAIL'); ?>
                    </p>
                </div>
                <div class="col-6 text-end">
                    <h4 class="text-primary fw-black mb-1 d-flex align-items-center justify-content-end gap-2">
                        <span class="material-symbols-outlined fs-3">receipt</span>
                        <span>FACTURA</span>
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
                    <p class="text-primary x-small fw-bold uppercase tracking-widest mb-3">Facturar a:</p>
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
                <h6 class="text-white x-small fw-bold uppercase tracking-widest mb-3">Instrucciones de Pago</h6>
                <p class="text-white-50 small mb-0">Transferencia Bancaria:<br>
                    <strong><?php echo \Core\Config::get('bank.account_name'); ?></strong><br>
                    Banco: <?php echo \Core\Config::get('bank.name'); ?><br>
                    Cuenta/CBU: <?php echo \Core\Config::get('bank.account_number'); ?><br>
                    Alias: <?php echo \Core\Config::get('bank.cbu_alias'); ?><br>
                    Referencia: <?php echo $invoice['invoice_number']; ?>
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="row g-4 no-print">
            <div class="col-md-6">
                <?php if ($invoice['status'] == 'unpaid' || $invoice['status'] == 'overdue' || $invoice['status'] == 'partial'): ?>

                    <?php if (!empty(\Core\Config::get('payment.mp_access_token'))): ?>
                        <div class="glass-morphism p-4 rounded-5 border-primary border-opacity-25 shadow-gold mb-4">
                            <h5 class="text-white fw-bold mb-3 small uppercase d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined text-primary">credit_card</span>
                                Pagar Online
                            </h5>
                            <form action="<?php echo url('invoice/payMp'); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                <div class="mb-3">
                                    <label class="text-white-50 x-small mb-2">Monto a Pagar</label>
                                    <?php $pending = $invoice['total'] - $invoice['paid_amount']; ?>
                                    <input type="number" step="0.01" name="amount"
                                        class="form-control bg-steel border-white-10 text-white shadow-none"
                                        value="<?php echo number_format($pending > 0 ? $pending : $invoice['total'], 2, '.', ''); ?>"
                                        max="<?php echo number_format($pending > 0 ? $pending : $invoice['total'], 2, '.', ''); ?>"
                                        required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold uppercase">Pagar con
                                    MercadoPago</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <div class="glass-morphism p-4 rounded-5 border-white-10">
                        <h5 class="text-white fw-bold mb-3 small uppercase">Reportar Pago</h5>
                        <p class="text-white-50 x-small mb-4">Sube tu comprobante de transferencia para que nuestro equipo
                            valide el pago y active tu servicio.</p>
                        <form action="<?php echo url('invoice/pay'); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                            <div class="mb-3">
                                <label class="text-white-50 x-small mb-2">Monto a Reportar (USD)</label>
                                <?php $pending = $invoice['total'] - $invoice['paid_amount']; ?>
                                <input type="number" step="0.01" name="amount"
                                    class="form-control bg-steel border-white-10 text-white shadow-none"
                                    value="<?php echo number_format($pending > 0 ? $pending : $invoice['total'], 2, '.', ''); ?>"
                                    max="<?php echo number_format($pending > 0 ? $pending : $invoice['total'], 2, '.', ''); ?>"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="text-white-50 x-small mb-2">Comprobante (Imagen/PDF)</label>
                                <input type="file" name="receipt" class="form-control bg-steel border-white-10 text-white"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold uppercase">Enviar
                                Comprobante</button>
                        </form>
                    </div>
                <?php elseif ($invoice['status'] == 'processing'): ?>
                    <div
                        class="glass-morphism p-4 rounded-5 border-info border-opacity-25 bg-info bg-opacity-10 text-center text-white">
                        <span class="material-symbols-outlined display-6 mb-3">pending_actions</span>
                        <h5 class="fw-bold uppercase small">Pago en Proceso</h5>
                        <p class="text-white-50 small mb-0">Hemos recibido tu comprobante. Nuestro staff lo verificará en
                            las próximas 24 horas hábiles.</p>
                    </div>
                <?php elseif ($invoice['status'] == 'paid'): ?>
                    <div
                        class="glass-morphism p-4 rounded-5 border-success border-opacity-25 bg-success bg-opacity-10 text-center text-white text-success">
                        <span class="material-symbols-outlined display-6 mb-3 text-success">check_circle</span>
                        <h5 class="fw-bold uppercase small text-success">Factura Pagada</h5>
                        <p class="text-white-50 small mb-0 text-success">¡Gracias! El pago ha sido verificado y tu servicio
                            está activo.</p>
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
        </div>
    </div>
</div>
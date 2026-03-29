<div class="row g-4">
    <div class="col-12 d-flex align-items-center justify-content-between mb-2">
        <div>
            <h2 class="text-white fw-black mb-1">Mis Facturas 🧾</h2>
            <p class="text-white-50">Consulta y gestiona tus estados de cuenta e ingresos.</p>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="col-12">
        <div class="glass-morphism rounded-5 border-white-10 overflow-hidden shadow-2xl">
            <div class="p-4 border-bottom border-white-10 bg-white-5">
                <h5 class="text-white h6 mb-0 fw-bold uppercase tracking-widest">Historial de Facturación</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-deep-black">
                        <tr class="x-small uppercase text-white-50 tracking-widest">
                            <th class="p-4 border-0 text-start">Acciones</th>
                            <th class="p-4 border-0">Nº Factura</th>
                            <th class="p-4 border-0">Ref. Presupuesto</th>
                            <th class="p-4 border-0">Fecha Emisión</th>
                            <th class="p-4 border-0">Total</th>
                            <th class="p-4 border-0">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td class="p-4 text-start">
                                    <a href="<?php echo url('invoice/show/' . $inv['id']); ?>"
                                        class="btn btn-outline-white btn-sm rounded-pill px-3 border-white-10 x-small uppercase fw-bold">
                                        Ver Factura
                                    </a>
                                </td>
                                <td class="p-4 text-white small fw-bold">
                                    <?php echo $inv['invoice_number']; ?>
                                </td>
                                <td class="p-4">
                                    <span class="x-small text-white-50">#
                                        <?php echo $inv['budget_number'] ?? 'N/A'; ?>
                                    </span>
                                </td>
                                <td class="p-4 text-white-50 x-small">
                                    <?php echo date('d/m/Y', strtotime($inv['issue_date'])); ?>
                                </td>
                                <td class="p-4 text-primary fw-bold">
                                    $
                                    <?php echo number_format($inv['total'], 2); ?>
                                </td>
                                <td class="p-4">
                                    <?php
                                    $invStatus = \App\Domain\Invoice\InvoiceStatus::fromString($inv['status']);
                                    ?>
                                    <span
                                        class="badge <?php echo $invStatus->getBadgeClass(); ?> x-small uppercase fw-bold px-2 py-1">
                                        <?php echo $invStatus->getLabel(); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="6" class="p-5 text-center text-white-50 italic">
                                    No tienes facturas generadas todavía.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
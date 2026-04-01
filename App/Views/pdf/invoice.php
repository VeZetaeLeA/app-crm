<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?= $invoice['invoice_number'] ?></title>
    <?php
        // PDF Color Tokens — resolved from .env via Config (Dompdf cannot load Google Fonts)
        $pdfPrimary = \Core\Config::get('ui.primary_color',    '#0ea5e9');
        $pdfGold    = \Core\Config::get('ui.gold_color',       '#D4AF37');
        $pdfBgDark  = \Core\Config::get('ui.color_bg_dark',    '#020617');
    ?>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            background-color: #fff;
        }
        .header {
            background-color: <?= $pdfBgDark ?>;
            color: #fff;
            padding: 40px;
            text-align: left;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
        .company-info h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .company-info p {
            margin: 0;
            font-size: 10px;
            color: <?= $pdfPrimary ?>; /* Accento de marca desde UI_PRIMARY_COLOR */
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .document-title {
            text-align: right;
        }
        .document-title h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 900;
            color: #fff;
        }
        .document-title p {
            margin: 0;
            font-size: 12px;
            color: rgba(255,255,255,0.6);
        }
        .content {
            padding: 40px;
        }
        .invoice-meta {
            margin-bottom: 40px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
        }
        .invoice-meta table {
            width: 100%;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: <?= $pdfPrimary ?>;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-label {
            font-size: 10px;
            color: #999;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 14px;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .status-paid { background-color: #dcfce7; color: #166534; }
        .status-pending { background-color: #fef9c3; color: #854d0e; }
        .status-unpaid { background-color: #fee2e2; color: #991b1b; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f1f5f9;
            padding: 12px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
        }
        .bottom-section {
            margin-top: 30px;
        }
        .bottom-section table {
            width: 100%;
        }
        .qr-box {
            text-align: left;
        }
        .qr-code {
            width: 100px;
            height: 100px;
        }
        .validation-text {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 5px;
            max-width: 150px;
        }
        .totals {
            width: 250px;
        }
        .total-row {
            padding: 8px 0;
        }
        .total-label {
            font-size: 12px;
            color: #64748b;
        }
        .total-value {
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
        .grand-total {
            background-color: <?= $pdfBgDark ?>;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .grand-total .total-label { color: <?= $pdfPrimary ?>; font-weight: bold; }
        .grand-total .total-value { color: #fff; font-size: 18px; }

        .footer {
            position: fixed;
            bottom: 30px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td width="70">
                    <?php if (!empty($logo_base64)): ?>
                        <img src="<?= $logo_base64 ?>" class="logo">
                    <?php endif; ?>
                </td>
                <td class="company-info">
                    <h1><?= \Core\Config::get('business.company_name') ?: 'Tu Empresa' ?></h1>
                    <p>Comprobante de Facturación Electrónica</p>
                </td>
                <td class="document-title">
                    <h2>FACTURA</h2>
                    <p>#<?= $invoice['invoice_number'] ?></p>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <div class="invoice-meta">
            <table>
                <tr>
                    <td width="40%">
                        <div class="section-title">Cliente</div>
                        <div class="info-value"><?= $invoice['client_name'] ?></div>
                        <div class="info-label"><?= $invoice['client_company'] ?></div>
                        <div class="info-label"><?= $invoice['client_email'] ?></div>
                        <?php if($invoice['client_phone']): ?>
                            <div class="info-label"><?= $invoice['client_phone'] ?></div>
                        <?php endif; ?>
                    </td>
                    <td width="30%">
                        <div class="section-title">Detalles</div>
                        <div class="info-label">Fecha Emisión:</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($invoice['created_at'])) ?></div>
                        <div class="info-label">Presupuesto Ref:</div>
                        <div class="info-value">#<?= $invoice['budget_number'] ?></div>
                    </td>
                    <td width="30%" align="right">
                        <div class="section-title">Estado de Pago</div>
                        <div class="status-badge status-<?= $invoice['status'] ?>">
                            <?= translateStatus($invoice['status']) ?>
                        </div>
                        <div style="margin-top: 10px;">
                            <div class="info-label">Monto Pagado:</div>
                            <div class="info-value">$<?= number_format($invoice['paid_amount'], 2) ?></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-title">Servicio / Concepto</div>
        <div class="info-value" style="margin-bottom: 20px;"><?= $invoice['service_reference'] ?></div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th width="100" align="right">Subtotal</th>
                    <th width="100" align="right">Impuesto</th>
                    <th width="100" align="right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cargos del Servicio según propuesta comercial vinculada (#<?= $invoice['budget_number'] ?>)</td>
                    <td align="right">$<?= number_format($invoice['subtotal'], 2) ?></td>
                    <td align="right">$<?= number_format($invoice['tax_amount'], 2) ?></td>
                    <td align="right">$<?= number_format($invoice['total'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="bottom-section">
            <table width="100%">
                <tr>
                    <td valign="top">
                        <div class="qr-box">
                            <img src="<?= $qr_base64 ?>" class="qr-code">
                            <div class="validation-text">
                                Escanee el código QR para validar la autenticidad de este comprobante en nuestro portal oficial.
                                <br>Validación Hash: <?= substr(md5($invoice['invoice_number'] . $invoice['created_at']), 0, 16) ?>
                            </div>
                        </div>
                    </td>
                    <td width="250" valign="top">
                        <div class="totals" style="margin-left: auto;">
                            <table width="100%">
                                <tr>
                                    <td class="total-label">Subtotal:</td>
                                    <td class="total-value">$<?= number_format($invoice['subtotal'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="total-label">IVA:</td>
                                    <td class="total-value">$<?= number_format($invoice['tax_amount'], 2) ?></td>
                                </tr>
                                <tr class="grand-total">
                                    <td class="total-label">TOTAL <?= $invoice['currency'] ?>:</td>
                                    <td class="total-value">$<?= number_format($invoice['total'], 2) ?></td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        Este documento es una factura válida emitida por <?= \Core\Config::get('business.company_name') ?>. 
        Sujeta a los términos y condiciones del contrato de servicio. 
        Para soporte o aclaraciones: <?= \Core\Config::get('business.company_mail') ?: 'contacto@tuempresa.com' ?>
    </div>
</body>
</html>

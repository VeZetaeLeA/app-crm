<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto <?= $budget['budget_number'] ?></title>
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
            color: <?= $pdfPrimary ?>;
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
        .client-info {
            margin-bottom: 40px;
        }
        .client-info table {
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
        .scope-box {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 12px;
            color: #475569;
        }
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
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .total-row {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
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
        .grand-total .total-value { color: #fff; font-size: 20px; }
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
                    <img src="<?= $logo_base64 ?>" class="logo">
                </td>
                <td class="company-info">
                    <h1><?= \Core\Config::get('business.company_name') ?: 'Tu Empresa' ?></h1>
                    <p><?= \Core\Config::get('business.company_slogan') ?: 'Arquitectos de tu Vanguardia Digital' ?></p>
                </td>
                <td class="document-title">
                    <h2>PRESUPUESTO</h2>
                    <p>#<?= $budget['budget_number'] ?> | Versión <?= $budget['version'] ?></p>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <div class="client-info">
            <table>
                <tr>
                    <td width="50%">
                        <div class="section-title">Preparado para</div>
                        <div class="info-value"><?= $budget['client_name'] ?></div>
                        <div class="info-label"><?= $budget['client_company'] ?></div>
                        <div class="info-label"><?= $budget['client_email'] ?></div>
                    </td>
                    <td width="50%" align="right">
                        <div class="section-title">Detalles del Documento</div>
                        <div class="info-label">Fecha de Emisión:</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($budget['created_at'])) ?></div>
                        <div class="info-label">Válido hasta:</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($budget['created_at'] . " + {$budget['valid_days']} days")) ?></div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-title">Servicio Solicitado</div>
        <div class="info-value" style="margin-bottom: 20px;"><?= $budget['service_reference'] ?: 'N/D' ?></div>

        <div class="section-title">Alcance de la Propuesta</div>
        <div class="scope-box">
            <?= nl2br($budget['scope']) ?>
        </div>

        <div class="section-title">Desglose de Inversión</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th width="80" align="center">Cant.</th>
                    <th width="100" align="right">Precio Unit.</th>
                    <th width="100" align="right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['description'] ?></td>
                        <td align="center"><?= number_format($item['quantity'], 2) ?></td>
                        <td align="right">$<?= number_format($item['unit_price'], 2) ?></td>
                        <td align="right">$<?= number_format($item['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <table width="100%">
                <tr>
                    <td class="total-label">Subtotal:</td>
                    <td class="total-value">$<?= number_format($budget['subtotal'], 2) ?></td>
                </tr>
                <tr>
                    <td class="total-label">IVA (<?= number_format($budget['tax_rate'], 0) ?>%):</td>
                    <td class="total-value">$<?= number_format($budget['tax_amount'], 2) ?></td>
                </tr>
                <tr class="grand-total">
                    <td class="total-label">TOTAL (<?= $budget['currency'] ?>):</td>
                    <td class="total-value">$<?= number_format($budget['total'], 2) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        Este documento es una propuesta comercial de <?= \Core\Config::get('business.company_name') ?>. 
        Válido por los términos especificados. Documento generado por el sistema de gestión <?= \Core\Config::get('business.company_name') ?: 'Tu Empresa' ?>.
    </div>
</body>
</html>

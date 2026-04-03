<?php
/**
 * Renderizador Local de Plantillas de Email - VeZetaeLeA OS
 * Script para auditar y previsualizar en vivo el motor Core\Mail
 * (SÓLO USO EN ENTORNO LOCAL)
 */

// Forzamos el flag para interceptar el método send y no mandar emails reales
putenv('MAIL_RENDER_MOCK=true');
$mockEmailOutput = '';

// Bootloader App
require_once __DIR__ . '/../public/index.php';

// Verificación de seguridad básica (sólo correr en localhost o dev)
$env = getenv('ENVIRONMENT') ?: 'local';
if ($env === 'production') {
    die("Acceso denegado: Herramienta inhabilitada en producción.");
}

use Core\Mail;

// Ejecutamos las llamadas mock para generar los cuerpos HTML

// 1. Welcome
Mail::sendWelcome('ceo@example.com', 'Jane Doe', 'TemporaPass123!');
// 2. Ticket Update
Mail::sendTicketUpdate('ceo@example.com', 'TCK-9901', 'Bajo Análisis Técnico');
// 3. Request Confirmation
Mail::sendRequestConfirmation('ceo@example.com', 'Jane Doe', 'TCK-9902', 'Falla de Servidor en Región US-East');
// 4. Budget Available
Mail::sendBudgetAvailable('ceo@example.com', 'Jane Doe', 'PRP-24A001', '1049');
// 5. Urgent Support
Mail::sendUrgentSupport('soporte@vezetaelea.com', 'Jane Doe (Acme Corp)', 'jane@acmecorp.com', 'TCK-CRIT-01');
// 6. Deliverable Ready
Mail::sendDeliverableReady('ceo@example.com', 'Jane Doe', 'Migración Cloud Phase 2', 'Arquitectura AWS Lambda Lista', 'Los microservicios han sido testeados.', 'DELIV-777');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizador de Correos VZLA</title>
    <!-- Incluimos font de app.php como prueba adicional (opcional por ser email, pero ayuda a preview local) -->
    <style>
        body {
            background-color: #f1f5f9;
            margin: 0;
            padding: 40px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 50px;
        }
        .header h1 {
            color: #0f172a;
        }
        .header p {
            color: #64748b;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Sistema Centralizado de Emails</h1>
        <p>Previsualizando en formato HTML (Mapeo Cero-Hardcoding activado)</p>
        <p>Variables dinámicas utilizadas: UI_PRIMARY_COLOR, UI_GOLD_COLOR, TYPOGRAPHY</p>
    </div>

    <!-- Se imprime todo el buffer global interceptado -->
    <?php echo $mockEmailOutput; ?>

</body>
</html>

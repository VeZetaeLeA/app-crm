<?php
namespace App\Listeners;

use App\Models\Notification;
use App\Events\LeadCreated;
use App\Events\InvoiceIssued;
use App\Events\ProjectStarted;

class NotificationListener
{
    public static function handleLeadCreated(LeadCreated $event)
    {
        $data = $event->getData();
        Notification::send($data['client_id'], 'ticket_created', 'Solicitud Recibida', 'Hemos recibido tu solicitud correctamente.', '/ticket/detail/' . $data['ticket_id']);
    }

    public static function handleInvoiceIssued(InvoiceIssued $event)
    {
        // Already handled in service for now to avoid double notifications during refactor, 
        // but we could move it here later.
    }

    public static function handleProjectStarted(ProjectStarted $event)
    {
        $data = $event->getData();
        Notification::send($data['client_id'], 'service_activated', 'Proyecto Iniciado', 'Tu proyecto ha sido activado y el Workspace está listo.', '/project/workspace');
    }
}

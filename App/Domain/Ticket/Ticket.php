<?php
namespace App\Domain\Ticket;

use App\Domain\Ticket\TicketStatus;

/**
 * Ticket Entity - Pure Domain Class
 */
class Ticket
{
    private $id;
    private $ticketNumber;
    private $clientId;
    private $status;
    private $priority;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->ticketNumber = $data['ticket_number'] ?? '';
        $this->clientId = $data['client_id'] ?? null;
        $this->status = TicketStatus::fromString($data['status'] ?? TicketStatus::OPEN);
        $this->priority = $data['priority'] ?? 'normal';
    }

    /**
     * Trigger a domain event (to be intercepted by RuleEngine via Listeners).
     */
    public function dispatchEvent(string $eventName, array $context = []): void
    {
        // En esta fase, preparamos la infraestructura.
        // El despachador será invocado cuando se definan eventos concretos.
        // \Core\EventDispatcher::dispatch(...)
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getStatus(): TicketStatus
    {
        return $this->status;
    }

    /**
     * Verifica si el ticket puede ser procesado por el staff (análisis, presupuesto, etc.)
     */
    public function canBeProcessed(): bool
    {
        return !$this->status->isClosed();
    }

    /**
     * Verifica si el cliente puede cerrar el ticket manualmente.
     */
    public function canBeClosedByClient(): bool
    {
        // Solo si no hay un servicio activo en ejecución avanzada
        return in_array($this->status->toString(), [
            TicketStatus::OPEN,
            TicketStatus::IN_ANALYSIS,
            TicketStatus::BUDGET_SENT,
            TicketStatus::BUDGET_REJECTED
        ]);
    }
}

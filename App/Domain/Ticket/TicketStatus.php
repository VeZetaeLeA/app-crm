<?php
namespace App\Domain\Ticket;

/**
 * Ticket Status - Value Object
 * Representa los estados válidos de un ticket y sus transiciones
 */
class TicketStatus
{
    // Estados válidos
    const OPEN = 'open';
    const IN_ANALYSIS = 'in_analysis';
    const BUDGET_SENT = 'budget_sent';
    const BUDGET_APPROVED = 'budget_approved';
    const BUDGET_REJECTED = 'budget_rejected';
    const INVOICED = 'invoiced';
    const PAYMENT_PENDING = 'payment_pending';
    const ACTIVE = 'active';
    const CLOSED = 'closed';
    const VOID = 'void';

    private $status;

    private function __construct(string $status)
    {
        if (!$this->isValid($status)) {
            throw new \InvalidArgumentException("Estado de ticket inválido: {$status}");
        }
        $this->status = $status;
    }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public static function open(): self
    {
        return new self(self::OPEN);
    }

    public static function inAnalysis(): self
    {
        return new self(self::IN_ANALYSIS);
    }

    public static function budgetSent(): self
    {
        return new self(self::BUDGET_SENT);
    }

    public static function budgetApproved(): self
    {
        return new self(self::BUDGET_APPROVED);
    }

    public static function budgetRejected(): self
    {
        return new self(self::BUDGET_REJECTED);
    }

    public static function invoiced(): self
    {
        return new self(self::INVOICED);
    }

    public static function paymentPending(): self
    {
        return new self(self::PAYMENT_PENDING);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function closed(): self
    {
        return new self(self::CLOSED);
    }

    public function toString(): string
    {
        return $this->status;
    }

    public function equals(TicketStatus $other): bool
    {
        return $this->status === $other->status;
    }

    /**
     * Verifica si el estado es válido
     */
    private function isValid(string $status): bool
    {
        return in_array($status, self::all());
    }

    /**
     * Verifica si puede transicionar a otro estado
     */
    public function canTransitionTo(TicketStatus $newStatus): bool
    {
        $transitions = [
            self::OPEN => [self::IN_ANALYSIS, self::CLOSED, self::VOID],
            self::IN_ANALYSIS => [self::BUDGET_SENT, self::CLOSED, self::VOID],
            self::BUDGET_SENT => [self::BUDGET_APPROVED, self::BUDGET_REJECTED],
            self::BUDGET_APPROVED => [self::INVOICED],
            self::BUDGET_REJECTED => [self::IN_ANALYSIS, self::CLOSED, self::VOID],
            self::INVOICED => [self::PAYMENT_PENDING],
            self::PAYMENT_PENDING => [self::ACTIVE],
            self::ACTIVE => [self::CLOSED, self::VOID],
            self::CLOSED => []
        ];

        return in_array($newStatus->toString(), $transitions[$this->status] ?? []);
    }

    /**
     * Obtiene los estados a los que puede transicionar
     */
    public function getValidTransitions(): array
    {
        $transitions = [
            self::OPEN => [self::IN_ANALYSIS, self::CLOSED, self::VOID],
            self::IN_ANALYSIS => [self::BUDGET_SENT, self::CLOSED, self::VOID],
            self::BUDGET_SENT => [self::BUDGET_APPROVED, self::BUDGET_REJECTED],
            self::BUDGET_APPROVED => [self::INVOICED],
            self::BUDGET_REJECTED => [self::IN_ANALYSIS, self::CLOSED, self::VOID],
            self::INVOICED => [self::PAYMENT_PENDING],
            self::PAYMENT_PENDING => [self::ACTIVE],
            self::ACTIVE => [self::CLOSED, self::VOID],
            self::CLOSED => []
        ];

        return $transitions[$this->status] ?? [];
    }

    /**
     * Verifica si el ticket requiere acción del staff
     */
    public function requiresStaffAction(): bool
    {
        return in_array($this->status, [
            self::OPEN,
            self::IN_ANALYSIS,
            self::BUDGET_APPROVED
        ]);
    }

    /**
     * Verifica si el ticket requiere acción del cliente
     */
    public function requiresClientAction(): bool
    {
        return in_array($this->status, [
            self::BUDGET_SENT,
            self::INVOICED,
            self::PAYMENT_PENDING
        ]);
    }

    /**
     * Verifica si el ticket está cerrado
     */
    public function isClosed(): bool
    {
        return $this->status === self::CLOSED;
    }

    /**
     * Verifica si el ticket está activo (servicio en ejecución)
     */
    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    /**
     * Obtiene el label legible del estado
     */
    public function getLabel(): string
    {
        $labels = [
            self::OPEN => 'Abierto',
            self::IN_ANALYSIS => 'En Análisis',
            self::BUDGET_SENT => 'Presupuesto Enviado',
            self::BUDGET_APPROVED => 'Presupuesto Aprobado',
            self::BUDGET_REJECTED => 'Presupuesto Rechazado',
            self::INVOICED => 'Facturado',
            self::PAYMENT_PENDING => 'Pago Pendiente',
            self::ACTIVE => 'Activo',
            self::CLOSED => 'Cerrado',
            self::VOID => 'Anulado'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Obtiene la clase CSS para el badge
     */
    public function getBadgeClass(): string
    {
        $classes = [
            self::OPEN => 'badge-info',
            self::IN_ANALYSIS => 'badge-warning',
            self::BUDGET_SENT => 'badge-primary',
            self::BUDGET_APPROVED => 'badge-success',
            self::BUDGET_REJECTED => 'badge-danger',
            self::INVOICED => 'badge-info',
            self::PAYMENT_PENDING => 'badge-warning',
            self::ACTIVE => 'badge-success',
            self::CLOSED => 'badge-secondary',
            self::VOID => 'badge-dark'
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Obtiene todos los estados válidos
     */
    public static function all(): array
    {
        return [
            self::OPEN,
            self::IN_ANALYSIS,
            self::BUDGET_SENT,
            self::BUDGET_APPROVED,
            self::BUDGET_REJECTED,
            self::INVOICED,
            self::PAYMENT_PENDING,
            self::ACTIVE,
            self::CLOSED,
            self::VOID
        ];
    }
}

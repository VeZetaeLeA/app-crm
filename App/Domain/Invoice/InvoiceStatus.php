<?php
namespace App\Domain\Invoice;

/**
 * Invoice Status - Value Object
 * Representa los estados válidos de una factura y sus transiciones
 */
class InvoiceStatus
{
    // Estados válidos (Sincronizado con BD + cancelled)
    const UNPAID = 'unpaid';
    const PROCESSING = 'processing';
    const PARTIAL = 'partial';
    const PAID = 'paid';
    const OVERDUE = 'overdue';
    const CANCELLED = 'cancelled';
    const VOID = 'void';

    private $status;

    private function __construct(string $status)
    {
        if (!$this->isValid($status)) {
            throw new \InvalidArgumentException("Estado de factura inválido: {$status}");
        }
        $this->status = $status;
    }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public static function unpaid(): self
    {
        return new self(self::UNPAID);
    }

    public static function processing(): self
    {
        return new self(self::PROCESSING);
    }

    public static function paid(): self
    {
        return new self(self::PAID);
    }

    public static function overdue(): self
    {
        return new self(self::OVERDUE);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public function toString(): string
    {
        return $this->status;
    }

    public function equals(InvoiceStatus $other): bool
    {
        return $this->status === $other->status;
    }

    private function isValid(string $status): bool
    {
        return in_array($status, self::all());
    }

    public function canTransitionTo(InvoiceStatus $newStatus): bool
    {
        $transitions = [
            self::UNPAID => [self::PROCESSING, self::PARTIAL, self::OVERDUE, self::CANCELLED, self::VOID],
            self::PROCESSING => [self::PARTIAL, self::PAID, self::UNPAID, self::VOID],
            self::PARTIAL => [self::PROCESSING, self::PAID, self::VOID],
            self::PAID => [self::VOID],
            self::OVERDUE => [self::PROCESSING, self::PARTIAL, self::PAID, self::CANCELLED, self::VOID],
            self::CANCELLED => [],
            self::VOID => []
        ];

        return in_array($newStatus->toString(), $transitions[$this->status] ?? []);
    }

    public function getValidTransitions(): array
    {
        $transitions = [
            self::UNPAID => [self::PROCESSING, self::PARTIAL, self::OVERDUE, self::CANCELLED, self::VOID],
            self::PROCESSING => [self::PARTIAL, self::PAID, self::UNPAID, self::VOID],
            self::PARTIAL => [self::PROCESSING, self::PAID, self::VOID],
            self::PAID => [self::VOID],
            self::OVERDUE => [self::PROCESSING, self::PARTIAL, self::PAID, self::CANCELLED, self::VOID],
            self::CANCELLED => [],
            self::VOID => []
        ];

        return $transitions[$this->status] ?? [];
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [self::UNPAID, self::PROCESSING, self::PARTIAL, self::OVERDUE]);
    }

    public function isOverdue(): bool
    {
        return $this->status === self::OVERDUE;
    }

    public function getLabel(): string
    {
        $labels = [
            self::UNPAID => 'No Pagada',
            self::PROCESSING => 'Procesando / En Revisión',
            self::PARTIAL => 'Pago Parcial',
            self::PAID => 'Pagada',
            self::OVERDUE => 'Vencida',
            self::CANCELLED => 'Cancelada',
            self::VOID => 'Anulada'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getBadgeClass(): string
    {
        $classes = [
            self::UNPAID => 'badge-warning',
            self::PROCESSING => 'badge-info',
            self::PARTIAL => 'badge-primary',
            self::PAID => 'badge-success',
            self::OVERDUE => 'badge-danger',
            self::CANCELLED => 'badge-secondary',
            self::VOID => 'badge-dark'
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    public static function all(): array
    {
        return [
            self::UNPAID,
            self::PROCESSING,
            self::PARTIAL,
            self::PAID,
            self::OVERDUE,
            self::CANCELLED,
            self::VOID
        ];
    }
}

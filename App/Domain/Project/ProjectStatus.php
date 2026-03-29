<?php
namespace App\Domain\Project;

/**
 * Project Status - Value Object
 * Representa los estados válidos de un proyecto y sus transiciones
 */
class ProjectStatus
{
    // Estados válidos
    const PENDING = 'pending';
    const IN_PROGRESS = 'in_progress';
    const ON_HOLD = 'on_hold';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';

    private $status;

    private function __construct(string $status)
    {
        if (!$this->isValid($status)) {
            throw new \InvalidArgumentException("Estado de proyecto inválido: {$status}");
        }
        $this->status = $status;
    }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function inProgress(): self
    {
        return new self(self::IN_PROGRESS);
    }

    public static function onHold(): self
    {
        return new self(self::ON_HOLD);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public function toString(): string
    {
        return $this->status;
    }

    public function equals(ProjectStatus $other): bool
    {
        return $this->status === $other->status;
    }

    /**
     * Verifica si el estado es válido
     */
    private function isValid(string $status): bool
    {
        return in_array($status, [
            self::PENDING,
            self::IN_PROGRESS,
            self::ON_HOLD,
            self::COMPLETED,
            self::CANCELLED
        ]);
    }

    /**
     * Verifica si puede transicionar a otro estado
     */
    public function canTransitionTo(ProjectStatus $newStatus): bool
    {
        $transitions = [
            self::PENDING => [self::IN_PROGRESS, self::CANCELLED],
            self::IN_PROGRESS => [self::ON_HOLD, self::COMPLETED, self::CANCELLED],
            self::ON_HOLD => [self::IN_PROGRESS, self::CANCELLED],
            self::COMPLETED => [],
            self::CANCELLED => []
        ];

        return in_array($newStatus->toString(), $transitions[$this->status] ?? []);
    }

    /**
     * Obtiene los estados a los que puede transicionar
     */
    public function getValidTransitions(): array
    {
        $transitions = [
            self::PENDING => [self::IN_PROGRESS, self::CANCELLED],
            self::IN_PROGRESS => [self::ON_HOLD, self::COMPLETED, self::CANCELLED],
            self::ON_HOLD => [self::IN_PROGRESS, self::CANCELLED],
            self::COMPLETED => [],
            self::CANCELLED => []
        ];

        return $transitions[$this->status] ?? [];
    }

    /**
     * Verifica si el proyecto está activo
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::PENDING, self::IN_PROGRESS, self::ON_HOLD]);
    }

    /**
     * Verifica si el proyecto está finalizado
     */
    public function isFinished(): bool
    {
        return in_array($this->status, [self::COMPLETED, self::CANCELLED]);
    }

    /**
     * Obtiene el label legible del estado
     */
    public function getLabel(): string
    {
        $labels = [
            self::PENDING => 'Pendiente',
            self::IN_PROGRESS => 'En Progreso',
            self::ON_HOLD => 'En Espera',
            self::COMPLETED => 'Completado',
            self::CANCELLED => 'Cancelado'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Obtiene la clase CSS para el badge
     */
    public function getBadgeClass(): string
    {
        $classes = [
            self::PENDING => 'badge-warning',
            self::IN_PROGRESS => 'badge-primary',
            self::ON_HOLD => 'badge-secondary',
            self::COMPLETED => 'badge-success',
            self::CANCELLED => 'badge-danger'
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Obtiene todos los estados válidos
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::IN_PROGRESS,
            self::ON_HOLD,
            self::COMPLETED,
            self::CANCELLED
        ];
    }
}

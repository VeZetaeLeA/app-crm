<?php
namespace App\Domain\Project;

use App\Domain\Project\ProjectStatus;

/**
 * Project Entity - Pure Domain Class
 * Representa un proyecto y sus reglas de negocio internas.
 * No contiene SQL ni lógica de persistencia.
 */
class Project
{
    private $id;
    private $title;
    private $clientId;
    private $status;
    private $assignedTo;
    private $createdAt;
    private $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->clientId = $data['client_id'] ?? null;
        $this->status = ProjectStatus::fromString($data['status'] ?? ProjectStatus::PENDING);
        $this->assignedTo = $data['assigned_to'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getClientId(): ?int
    {
        return $this->clientId;
    }
    public function getStatus(): ProjectStatus
    {
        return $this->status;
    }
    public function getAssignedTo(): ?int
    {
        return $this->assignedTo;
    }

    /**
     * Regla de negocio: ¿El proyecto está en un estado que permite edición?
     */
    public function canBeEdited(): bool
    {
        return !$this->status->isFinished();
    }

    /**
     * Regla de negocio: ¿Puede este proyecto ser asignado a un staff?
     */
    public function canBeAssigned(): bool
    {
        return $this->status->equals(ProjectStatus::pending()) ||
            $this->status->equals(ProjectStatus::onHold());
    }

    /**
     * Transiciona el estado del proyecto verificando la validez del flujo
     */
    public function transitionTo(ProjectStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new \DomainException("No se puede pasar de {$this->status->toString()} a {$newStatus->toString()}");
        }
        $this->status = $newStatus;
    }
}

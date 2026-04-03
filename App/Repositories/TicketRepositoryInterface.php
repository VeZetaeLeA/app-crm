<?php
declare(strict_types=1);

namespace App\Repositories;

interface TicketRepositoryInterface extends RepositoryInterface
{
    public function getRecentWithClients(int $limit = 10, array $excludeStatuses = []): array;
    public function getStats(): array;
    public function getDistribution(): array;
    public function createTicket(array $data): int;
    public function updateStatus(int $id, string $status): bool;
    public function assignTicket(int $id, int $staffId): bool;
    public function getTicketWithClientAndPlan(int $id): ?array;
    public function getClientByEmail(string $email): ?array;
    public function getClientById(int $id): ?array;
    public function getAll(array $filters = []): array;
    public function getById(int $id): ?array;
    public function getMessages(int $ticketId): array;
    public function getTasks(int $ticketId): array;
    
    // 🧠 AI Features (GAI-04, GAI-05) & SLA
    public function updateAiAnalysis(int $ticketId, string $sentiment, array $analysis): bool;
    public function updatePriority(int $ticketId, string $priority): bool;
    public function createTask(int $ticketId, string $description): int;
    public function createMessage(int $ticketId, ?int $userId, string $message, string $messageType = 'client'): int;
}

<?php
namespace App\Repositories;

interface TicketRepositoryInterface extends RepositoryInterface
{
    public function getRecentWithClients(int $limit = 10, array $excludeStatuses = []);
    public function getStats(): array;
    public function getDistribution(): array;
    public function createTicket(array $data): int;
    public function updateStatus(int $id, string $status): bool;
    public function assignTicket(int $id, int $staffId): bool;
    public function getTicketWithClientAndPlan(int $id): ?array;
    public function getClientByEmail(string $email): ?array;
    public function getClientById(int $id): ?array;
    public function createClient(array $data): int;
}

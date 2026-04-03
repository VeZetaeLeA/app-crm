<?php
declare(strict_types=1);

namespace App\Repositories;

interface ProjectRepositoryInterface extends RepositoryInterface
{
    public function getActiveServicesByClient(int $clientId): array;
    public function getAllActiveServices(): array;
    public function getServiceDetail(int $id): ?array;
    public function getDeliverablesByService(int $serviceId): array;
    public function updateServiceScope(int $serviceId, int $totalDeliverables): bool;
    public function addDeliverable(array $data): int;
    public function getDeliverable(int $id): ?array;
    public function updateDeliverableStatus(int $id, string $status, int $reviewerId, ?string $notes): bool;
    public function deleteDeliverable(int $id): bool;
}

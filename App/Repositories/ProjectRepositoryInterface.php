<?php
namespace App\Repositories;

interface ProjectRepositoryInterface extends RepositoryInterface
{
    public function getActiveServicesByClient($clientId);
    public function getAllActiveServices();
    public function getServiceDetail($id);
    public function getDeliverablesByService($serviceId);
    public function updateServiceScope($serviceId, $totalDeliverables);
    public function addDeliverable($data);
    public function getDeliverable($id);
    public function updateDeliverableStatus($id, $status, $reviewerId, $notes);
    public function deleteDeliverable($id);
}

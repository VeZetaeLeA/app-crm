<?php
namespace App\Services;

use App\Domain\Project\Project;
use App\Domain\Project\ProjectStatus;
use App\Domain\Project\ProjectPolicy;
use App\Services\AuditService;
use Core\Database;

/**
 * Project Service
 * Orquesta la gestión de proyectos integrando Dominio y Persistencia.
 */
class ProjectService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Cambia el estado de un proyecto validando el flujo de dominio.
     */
    public function updateStatus(int $projectId, string $newStatusStr, array $currentUser): array
    {
        $projectData = $this->findProjectById($projectId);
        if (!$projectData) {
            return ['success' => false, 'error' => 'Proyecto no encontrado'];
        }

        // 1. Autorización mediante Policy
        if (!ProjectPolicy::updateStatus($currentUser, $projectData)) {
            AuditService::log('unauthorized_status_change_attempt', ['project_id' => $projectId], 'WARN');
            return ['success' => false, 'error' => 'No autorizado para cambiar el estado'];
        }

        // 2. Lógica de Dominio
        try {
            $project = new Project($projectData);
            $newStatus = ProjectStatus::fromString($newStatusStr);
            $project->transitionTo($newStatus);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        // 3. Persistencia
        $sql = "UPDATE active_services SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$newStatusStr, $projectId]);

        // 4. Auditoría
        AuditService::log('project_status_updated', [
            'project_id' => $projectId,
            'old_status' => $projectData['status'],
            'new_status' => $newStatusStr
        ]);

        return ['success' => true];
    }

    private function findProjectById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM active_services WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}

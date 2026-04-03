<?php
declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;

class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    public function getActiveServicesByClient(int $clientId): array
    {
        $stmt = $this->db->prepare("SELECT s.*, p.name as plan_name,
                             i.total as invoice_total,
                             i.paid_amount as invoice_paid,
                             (i.total - i.paid_amount) as invoice_pending,
                             i.status as invoice_status,
                             i.id as invoice_id_ref,
                             (SELECT COUNT(*) FROM project_deliverables pd WHERE pd.active_service_id = s.id) as current_deliverables
                             FROM active_services s
                             JOIN service_plans p ON s.service_plan_id = p.id
                             LEFT JOIN invoices i ON s.invoice_id = i.id
                             WHERE s.client_id = ? AND s.status = 'active'");
        $stmt->execute([$clientId]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($services as &$s) {
            $s['progress_percent'] = ($s['total_deliverables'] > 0)
                ? round(($s['current_deliverables'] / $s['total_deliverables']) * 100)
                : 0;
            if (!isset($s['invoice_total'])) {
                $s['invoice_total'] = 0;
                $s['invoice_paid'] = 0;
                $s['invoice_pending'] = 0;
                $s['invoice_status'] = 'draft';
                $s['invoice_id_ref'] = 0;
            }
        }
        return $services;
    }

    public function getAllActiveServices(): array
    {
        $stmt = $this->db->query("SELECT s.*, u.name as client_name, p.name as plan_name,
                           i.total as invoice_total,
                           i.paid_amount as invoice_paid,
                           (i.total - i.paid_amount) as invoice_pending,
                           i.status as invoice_status,
                           i.id as invoice_id_ref
                           FROM active_services s
                           JOIN users u ON s.client_id = u.id
                           JOIN service_plans p ON s.service_plan_id = p.id
                           LEFT JOIN invoices i ON s.invoice_id = i.id
                           ORDER BY s.created_at DESC");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($services as &$s) {
            if (!isset($s['invoice_total'])) {
                $s['invoice_total'] = 0;
                $s['invoice_paid'] = 0;
                $s['invoice_pending'] = 0;
                $s['invoice_status'] = 'draft';
                $s['invoice_id_ref'] = 0;
            }
        }
        return $services;
    }

    public function getServiceDetail(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT s.*, u.name as client_name, u.email as client_email, p.name as plan_name
                             FROM active_services s
                             JOIN users u ON s.client_id = u.id
                             JOIN service_plans p ON s.service_plan_id = p.id
                             WHERE s.id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    public function getDeliverablesByService(int $serviceId): array
    {
        $stmt = $this->db->prepare("SELECT d.*, u.name as author_name, rv.name as reviewer_name
                             FROM project_deliverables d
                             JOIN users u ON d.uploaded_by = u.id
                             LEFT JOIN users rv ON d.reviewed_by = rv.id
                             WHERE d.active_service_id = ? ORDER BY d.created_at DESC");
        $stmt->execute([$serviceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function updateServiceScope(int $serviceId, int $totalDeliverables): bool
    {
        $stmt = $this->db->prepare("UPDATE active_services SET total_deliverables = ? WHERE id = ?");
        return $stmt->execute([$totalDeliverables, $serviceId]);
    }

    public function addDeliverable(array $data): int
    {
        $sql = "INSERT INTO project_deliverables
                    (active_service_id, uploaded_by, title, description, filename, filepath, file_type, file_size, version, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending_review')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['active_service_id'],
            $data['uploaded_by'],
            $data['title'],
            $data['description'],
            $data['filename'],
            $data['filepath'],
            $data['file_type'],
            $data['file_size'],
            $data['version']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getDeliverable(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT d.*, s.client_id, s.name as service_name, s.id as service_id
                              FROM project_deliverables d
                              JOIN active_services s ON d.active_service_id = s.id
                              WHERE d.id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    public function updateDeliverableStatus(int $id, string $status, int $reviewerId, ?string $notes): bool
    {
        $stmt = $this->db->prepare("UPDATE project_deliverables
                             SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_notes = ?
                             WHERE id = ?");
        return $stmt->execute([$status, $reviewerId, $notes, $id]);
    }

    public function deleteDeliverable(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM project_deliverables WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

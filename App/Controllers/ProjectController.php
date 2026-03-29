<?php
namespace App\Controllers;

use Core\Controller;
use Core\Database;
use Core\Auth;
use Core\Session;
use PDO;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('2fa');
    }

    public function workspace()
    {
        $db = Database::getInstance()->getConnection();
        $user = Auth::user();

        if (Auth::isClient()) {
            $stmt = $db->prepare("SELECT s.*, p.name as plan_name,
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
            $stmt->execute([$user['id']]);
            $services = $stmt->fetchAll();

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

            $deliverables = [];
            foreach ($services as $service) {
                $stmt = $db->prepare("SELECT * FROM project_deliverables WHERE active_service_id = ? ORDER BY created_at DESC");
                $stmt->execute([$service['id']]);
                $deliverables[$service['id']] = $stmt->fetchAll();
            }

            $this->viewLayout('client/project/workspace', 'client', [
                'title' => 'Mi Workspace de Proyecto | ' . \Core\Config::get('business.company_name'),
                'services' => $services,
                'deliverables' => $deliverables
            ]);
        } else {
            $stmt = $db->query("SELECT s.*, u.name as client_name, p.name as plan_name,
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
            $services = $stmt->fetchAll();

            foreach ($services as &$s) {
                if (!isset($s['invoice_total'])) {
                    $s['invoice_total'] = 0;
                    $s['invoice_paid'] = 0;
                    $s['invoice_pending'] = 0;
                    $s['invoice_status'] = 'draft';
                    $s['invoice_id_ref'] = 0;
                }
            }

            $this->viewLayout(Auth::role() . '/project/manage', Auth::role(), [
                'title' => 'Gestión de Workspaces | ' . \Core\Config::get('business.company_name'),
                'services' => $services
            ]);
        }
    }

    /**
     * Manage a specific service workspace (Staff/Admin)
     */
    public function manage($id)
    {
        if (Auth::isClient())
            $this->redirect('/project/workspace');

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT s.*, u.name as client_name, p.name as plan_name
                             FROM active_services s
                             JOIN users u ON s.client_id = u.id
                             JOIN service_plans p ON s.service_plan_id = p.id
                             WHERE s.id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch();

        if (!$service)
            $this->redirect('/project/workspace');

        $stmt = $db->prepare("SELECT d.*, u.name as author_name
                             FROM project_deliverables d
                             JOIN users u ON d.uploaded_by = u.id
                             WHERE d.active_service_id = ? ORDER BY d.created_at DESC");
        $stmt->execute([$id]);
        $deliverables = $stmt->fetchAll();

        $this->viewLayout(Auth::role() . '/project/detail', Auth::role(), [
            'title' => 'Workspace: ' . $service['name'],
            'service' => $service,
            'deliverables' => $deliverables
        ]);
    }

    /**
     * Upload Deliverable + Notify Client (SPRINT 2.1)
     */
    public function upload()
    {
        if (Auth::isClient() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/project/workspace');
        }

        $service_id = $_POST['active_service_id'];
        $title      = $_POST['title'];
        $description = $_POST['description'];
        $version    = $_POST['version'] ?? '1.0';
        $type       = $_POST['file_type'] ?? 'other';

        if (isset($_FILES['deliverable']) && $_FILES['deliverable']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['deliverable'];

            $maxSize = \Core\Config::get('limits.max_upload_size');
            $errors  = \Core\Validator::validateFile($file, $maxSize, ['pdf', 'zip', 'zipx', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']);

            if (!empty($errors)) {
                Session::flash('error', implode(' ', $errors));
                $this->redirect('/project/manage/' . $service_id);
                return;
            }

            $secureFilename = \Core\Validator::generateSecureFileName($file['name']);

            $targetDir = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR
                . 'storage' . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $service_id;

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $secureFilename;
            $dbFilepath = '/storage/projects/' . $service_id . '/' . $secureFilename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $db  = Database::getInstance()->getConnection();
                $sql = "INSERT INTO project_deliverables
                            (active_service_id, uploaded_by, title, description, filename, filepath, file_type, file_size, version, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending_review')";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $service_id,
                    Auth::user()['id'],
                    $title,
                    $description,
                    $file['name'],
                    $dbFilepath,
                    $type,
                    $file['size'],
                    $version
                ]);

                $deliverableId = $db->lastInsertId();

                // ============================================================
                // SPRINT 2.1 — Notificación al cliente al subir entregable
                // ============================================================
                $stmt = $db->prepare("SELECT s.client_id, u.email, u.name as client_name, s.name as service_name
                                     FROM active_services s
                                     JOIN users u ON s.client_id = u.id
                                     WHERE s.id = ?");
                $stmt->execute([$service_id]);
                $serviceData = $stmt->fetch();

                if ($serviceData) {
                    \Core\Mail::sendDeliverableReady(
                        $serviceData['email'],
                        $serviceData['client_name'],
                        $serviceData['service_name'],
                        $title,
                        $description,
                        $deliverableId
                    );

                    \App\Models\Notification::send(
                        $serviceData['client_id'],
                        'deliverable_ready',
                        '📦 Nuevo Entregable Disponible',
                        "Se subio el entregable \"{$title}\" en tu proyecto \"{$serviceData['service_name']}\". Revisalo y aprueba o rechaza.",
                        '/project/workspace'
                    );
                }

                Session::flash('success', 'Entregable subido correctamente. El cliente ha sido notificado.');
            } else {
                Session::flash('error', 'Error al mover el archivo al servidor. Verifica permisos del directorio storage.');
            }
        }

        $this->redirect('/project/manage/' . $service_id);
    }

    /**
     * Approve or Reject a Deliverable — Cliente (SPRINT 2.2)
     */
    public function review($id)
    {
        if (!Auth::isClient() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/project/workspace');
        }

        $action = $_POST['action'] ?? '';
        $notes  = trim($_POST['review_notes'] ?? '');

        if (!in_array($action, ['approve', 'reject'])) {
            Session::flash('error', 'Accion invalida.');
            $this->redirect('/project/workspace');
            return;
        }

        $db   = Database::getInstance()->getConnection();
        $user = Auth::user();

        $stmt = $db->prepare("SELECT d.*, s.client_id, s.name as service_name, s.id as service_id
                             FROM project_deliverables d
                             JOIN active_services s ON d.active_service_id = s.id
                             WHERE d.id = ?");
        $stmt->execute([$id]);
        $deliverable = $stmt->fetch();

        if (!$deliverable || $deliverable['client_id'] != $user['id']) {
            Session::flash('error', 'No tienes permiso para revisar este entregable.');
            $this->redirect('/project/workspace');
            return;
        }

        $newStatus = $action === 'approve' ? 'approved' : 'rejected';

        $stmt = $db->prepare("UPDATE project_deliverables
                             SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_notes = ?
                             WHERE id = ?");
        $stmt->execute([$newStatus, $user['id'], $notes, $id]);

        $staffStmt = $db->query("SELECT id FROM users WHERE role IN ('admin', 'staff')");
        $staff     = $staffStmt->fetchAll();
        $actionLabel = $action === 'approve' ? 'aprobado' : 'rechazado';
        $emoji       = $action === 'approve' ? '✅' : '❌';
        foreach ($staff as $s) {
            \App\Models\Notification::send(
                $s['id'],
                'deliverable_reviewed',
                "{$emoji} Entregable {$actionLabel}",
                "El cliente {$user['name']} ha {$actionLabel} \"{$deliverable['title']}\" del proyecto \"{$deliverable['service_name']}\"." . ($notes ? " Notas: {$notes}" : ''),
                '/project/manage/' . $deliverable['service_id']
            );
        }

        $msg = $action === 'approve' ? 'Entregable aprobado exitosamente.' : 'Entregable rechazado. El equipo fue notificado.';
        Session::flash('success', $msg);
        $this->redirect('/project/workspace');
    }

    /**
     * Timeline de Proyecto (SPRINT 2.4)
     */
    public function timeline($id)
    {
        if (Auth::isClient())
            $this->redirect('/project/workspace');

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT s.*, u.name as client_name, p.name as plan_name
                             FROM active_services s
                             JOIN users u ON s.client_id = u.id
                             JOIN service_plans p ON s.service_plan_id = p.id
                             WHERE s.id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch();

        if (!$service)
            $this->redirect('/project/workspace');

        $stmt = $db->prepare("SELECT d.*, u.name as author_name, rv.name as reviewer_name
                             FROM project_deliverables d
                             JOIN users u ON d.uploaded_by = u.id
                             LEFT JOIN users rv ON d.reviewed_by = rv.id
                             WHERE d.active_service_id = ? ORDER BY d.created_at ASC");
        $stmt->execute([$id]);
        $deliverables = $stmt->fetchAll();

        $this->viewLayout(Auth::role() . '/project/timeline', Auth::role(), [
            'title' => 'Timeline: ' . $service['name'],
            'service' => $service,
            'deliverables' => $deliverables
        ]);
    }

    /**
     * Secure File Download
     */
    public function download($id)
    {

        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT d.*, s.client_id FROM project_deliverables d
                              JOIN active_services s ON d.active_service_id = s.id
                              WHERE d.id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();

        if (!$file) {
            Session::flash('error', 'Archivo no encontrado.');
            $this->redirect('/project/workspace');
        }

        if (Auth::isClient() && $file['client_id'] != Auth::user()['id']) {
            Session::flash('error', 'No tienes permiso para descargar este archivo.');
            $this->redirect('/project/workspace');
        }

        $relativePath = ltrim(str_replace('/', DIRECTORY_SEPARATOR, $file['filepath']), DIRECTORY_SEPARATOR);
        $physicalPath = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $relativePath;

        if (!file_exists($physicalPath)) {
            error_log('[' . \Core\Config::get('business.company_name') . '] Download 404 - filepath en BD: ' . $file['filepath']);
            error_log('[' . \Core\Config::get('business.company_name') . '] Download 404 - ruta fisica buscada: ' . $physicalPath);
            Session::flash('error', 'El archivo no se encontro en el servidor.');
            $this->redirect('/project/workspace');
        }

        $mimeType     = mime_content_type($physicalPath) ?: 'application/octet-stream';
        $originalName = $file['filename'] ?: basename($file['filepath']);

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . rawurlencode($originalName) . '"');
        header('Content-Length: ' . filesize($physicalPath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($physicalPath);
        exit;
    }

    /**
     * Delete Deliverable
     */
    public function delete($id)
    {
        if (Auth::isClient())
            $this->redirect('/project/workspace');

        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM project_deliverables WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();

        if ($file) {
            $fullPath = 'public' . $file['filepath'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $stmt = $db->prepare("DELETE FROM project_deliverables WHERE id = ?");
            $stmt->execute([$id]);
            Session::flash('success', 'Entregable eliminado.');
            $this->redirect('/project/manage/' . $file['active_service_id']);
        }

        $this->redirect('/project/workspace');
    }

    /**
     * Update Project Scope
     */
    public function updateScope()
    {
        if (Auth::isClient() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/project/workspace');
        }

        $service_id = $_POST['active_service_id'];
        $total      = (int) $_POST['total_deliverables'];

        if ($service_id) {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE active_services SET total_deliverables = ? WHERE id = ?");
            $stmt->execute([$total, $service_id]);

            \Core\SecurityLogger::log('project_scope_updated', [
                'service_id'        => $service_id,
                'total_deliverables' => $total
            ]);

            Session::flash('success', 'Alcance del proyecto actualizado correctamente.');
            $this->redirect('/project/manage/' . $service_id);
        }

        $this->redirect('/project/workspace');
    }

    /**
     * Export projects to CSV (Admin/Staff sees all, Client sees own)
     */
    public function exportCsv()
    {
        if (!Auth::check())
            $this->redirect('/auth/login');

        $db = Database::getInstance()->getConnection();
        $user = Auth::user();

        if (Auth::isClient()) {
            $sql = "SELECT s.name, p.name as plan_name, s.status, s.total_deliverables, s.created_at 
                    FROM active_services s 
                    JOIN service_plans p ON s.service_plan_id = p.id 
                    WHERE s.client_id = ? AND s.status = 'active'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user['id']]);
        } else {
            $sql = "SELECT s.name, u.name as client_name, p.name as plan_name, s.status, s.total_deliverables, s.created_at 
                    FROM active_services s 
                    JOIN users u ON s.client_id = u.id 
                    JOIN service_plans p ON s.service_plan_id = p.id 
                    ORDER BY s.created_at DESC";
            $stmt = $db->query($sql);
        }

        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $headers = Auth::isClient() 
            ? ['Proyecto', 'Plan', 'Estado', 'Entregables Totales', 'Fecha Inicio']
            : ['Proyecto', 'Cliente', 'Plan', 'Estado', 'Entregables Totales', 'Fecha Inicio'];

        \App\Utils\CsvExporter::export('proyectos_' . date('Ymd'), $headers, $projects);
    }
}

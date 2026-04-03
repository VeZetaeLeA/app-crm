<?php
namespace App\Controllers;

use Core\Controller;
use Core\Database;
use Core\Auth;
use Core\Session;
use PDO;
use App\Repositories\ProjectRepository;

class ProjectController extends Controller
{
    private $projectRepo;

    public function __construct()
    {
        $this->projectRepo = new ProjectRepository(Database::getInstance()->getConnection());
        $this->middleware('auth');
        $this->middleware('2fa');
    }


    public function workspace()
    {
        $user = Auth::user();

        if (Auth::isClient()) {
            $services = $this->projectRepo->getActiveServicesByClient($user['id']);
            
            $deliverables = [];
            foreach ($services as $service) {
                $deliverables[$service['id']] = $this->projectRepo->getDeliverablesByService($service['id']);
            }

            $this->viewLayout('client/project/workspace', 'client', [
                'title' => 'Mi Workspace de Proyecto | ' . \Core\Config::get('business.company_name'),
                'services' => $services,
                'deliverables' => $deliverables
            ]);
        } else {
            $services = $this->projectRepo->getAllActiveServices();

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

        $service = $this->projectRepo->getServiceDetail($id);

        if (!$service)
            $this->redirect('/project/workspace');

        $deliverables = $this->projectRepo->getDeliverablesByService($id);

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
                $deliverableId = $this->projectRepo->addDeliverable([
                    'active_service_id' => $service_id,
                    'uploaded_by' => Auth::user()['id'],
                    'title'       => $title,
                    'description' => $description,
                    'filename'    => $file['name'],
                    'filepath'    => $dbFilepath,
                    'file_type'   => $type,
                    'file_size'   => $file['size'],
                    'version'     => $version
                ]);

                // ============================================================
                // SPRINT 2.1 — Notificación al cliente al subir entregable
                // ============================================================
                $serviceData = $this->projectRepo->getServiceDetail($service_id);


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

        $deliverable = $this->projectRepo->getDeliverable($id);

        if (!$deliverable || $deliverable['client_id'] != $user['id']) {
            Session::flash('error', 'No tienes permiso para revisar este entregable.');
            $this->redirect('/project/workspace');
            return;
        }

        $newStatus = $action === 'approve' ? 'approved' : 'rejected';

        $this->projectRepo->updateDeliverableStatus($id, $newStatus, $user['id'], $notes);

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

        $service = $this->projectRepo->getServiceDetail($id);

        if (!$service)
            $this->redirect('/project/workspace');

        // Note: Repository returns DESC usually, for timeline we might want ASC or handle in Repo
        // For simplicity we use the same repo but we could add getTimeline()
        $deliverables = $this->projectRepo->getDeliverablesByService($id);
        $deliverables = array_reverse($deliverables); // Simple flip for ASC timeline

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
        $file = $this->projectRepo->getDeliverable($id);


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

        $file = $this->projectRepo->getDeliverable($id);

        if ($file) {
            $fullPath = 'public' . $file['filepath'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $this->projectRepo->deleteDeliverable($id);
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
            $this->projectRepo->updateServiceScope($service_id, $total);


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

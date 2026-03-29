<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use Core\Auth;
use Core\Session;
use PDO;

/**
 * User CMS Controller
 * Manages user accounts, roles, profiles, and life-cycle from the Admin Panel.
 */
class UsersController extends Controller
{
    /**
     * Initializes the controller, enforcing admin-only access.
     */
    public function __construct()
    {
        if (!Auth::can('manage_users')) {
            Session::flash('error', 'Acceso denegado. Se requieren permisos de Gestión de Usuarios.');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Displays a list of all users in the system.
     * 
     * @return void
     */
    public function index()
    {
        $db = Database::getInstance()->getConnection();

        $users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

        $this->viewLayout('admin/users/index', 'admin', [
            'title' => 'Gestión de Usuarios | ' . \Core\Config::get('business.company_name'),
            'users' => $users
        ]);
    }

    public function updateStatus()
    {
        $db = Database::getInstance()->getConnection();
        $id = $_POST['user_id'];
        $status = $_POST['status']; // For now we use is_active or similar if exists. 
        // In our current schema we might not have 'status' field, let's check.
        // Wait, I should verify the schema first.

        $this->redirect('/admin/users');
    }

    public function updateRole()
    {
        $db = Database::getInstance()->getConnection();
        $id = $_POST['user_id'];
        $role = $_POST['role'];

        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$role, $id]);

        Session::flash('success', 'Rol de usuario actualizado.');
        $this->redirect('/admin/users');
    }

    public function edit($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            Session::flash('error', 'Usuario no encontrado.');
            $this->redirect('/admin/users');
        }

        $this->viewLayout('admin/users/edit', 'admin', [
            'title' => 'Editar Usuario | ' . \Core\Config::get('business.company_name'),
            'user' => $user
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
        }

        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $company = $_POST['company'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $password = $_POST['password'] ?? '';

        $db = Database::getInstance()->getConnection();

        // Dynamic update script
        $sql = "UPDATE users SET name = ?, email = ?, role = ?, company = ?, phone = ?, is_active = ?, updated_at = NOW()";
        $params = [$name, $email, $role, $company, $phone, $is_active];

        if (!empty($password)) {
            $sql .= ", password = ?";
            $params[] = Auth::hashPassword($password);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            $msg = 'Usuario actualizado correctamente';
            if (!empty($password))
                $msg .= ' (nueva contraseña establecida)';
            Session::flash('success', $msg . '.');
        } else {
            Session::flash('error', 'Error al actualizar el usuario.');
        }

        $this->redirect('/admin/users');
    }

    public function delete($id)
    {
        $db = Database::getInstance()->getConnection();

        if ($id == Auth::user()['id']) {
            Session::flash('error', 'No puedes desactivar tu propia cuenta.');
            $this->redirect('/admin/users');
        }

        $stmt = $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'El acceso del usuario ha sido revocado (Cuenta desactivada).');
        $this->redirect('/admin/users');
    }

    /**
     * Permanent Hard Delete
     */
    public function destroy($id = null)
    {
        if (!$id) {
            Session::flash('error', 'ID de usuario no proporcionado.');
            $this->redirect('/admin/users');
            return;
        }

        $db = Database::getInstance()->getConnection();

        if ($id == Auth::user()['id']) {
            Session::flash('error', 'No puedes eliminar tu propia cuenta.');
            $this->redirect('/admin/users');
            return;
        }

        try {
            $db->beginTransaction();

            // 1. Get all tickets associated with this user (if client)
            $stmt = $db->prepare("SELECT id FROM tickets WHERE client_id = ?");
            $stmt->execute([$id]);
            $ticketIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($ticketIds)) {
                $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));

                // Leaf nodes: Receipts & Services
                $db->prepare("DELETE FROM active_services WHERE ticket_id IN ($placeholders)")->execute($ticketIds);

                // Get invoices linked to these tickets
                $stmt = $db->prepare("SELECT id FROM invoices WHERE budget_id IN (SELECT id FROM budgets WHERE ticket_id IN ($placeholders))");
                $stmt->execute($ticketIds);
                $invoiceIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($invoiceIds)) {
                    $invPlaceholder = implode(',', array_fill(0, count($invoiceIds), '?'));
                    $db->prepare("DELETE FROM payment_receipts WHERE invoice_id IN ($invPlaceholder)")->execute($invoiceIds);
                    $db->prepare("DELETE FROM active_services WHERE invoice_id IN ($invPlaceholder)")->execute($invoiceIds);
                    $db->prepare("DELETE FROM invoices WHERE id IN ($invPlaceholder)")->execute($invoiceIds);
                }

                $db->prepare("DELETE FROM budgets WHERE ticket_id IN ($placeholders)")->execute($ticketIds);
                $db->prepare("DELETE FROM chat_messages WHERE ticket_id IN ($placeholders)")->execute($ticketIds);
                $db->prepare("DELETE FROM ticket_attachments WHERE ticket_id IN ($placeholders)")->execute($ticketIds);
                $db->prepare("DELETE FROM tickets WHERE id IN ($placeholders)")->execute($ticketIds);
            }

            // 2. Additional cleanup for cases where user is STAFF (creator/uploaded)
            $db->prepare("UPDATE tickets SET assigned_to = NULL WHERE assigned_to = ?")->execute([$id]);
            $db->prepare("DELETE FROM active_services WHERE activated_by = ? OR client_id = ?")->execute([$id, $id]);
            $db->prepare("DELETE FROM payment_receipts WHERE uploaded_by = ? OR verified_by = ?")->execute([$id, $id]);
            $db->prepare("DELETE FROM invoices WHERE created_by = ? OR client_id = ?")->execute([$id, $id]);
            $db->prepare("DELETE FROM budgets WHERE created_by = ?")->execute([$id]);
            $db->prepare("DELETE FROM chat_messages WHERE user_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM ticket_attachments WHERE user_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM blog_posts WHERE author_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$id]);
            $db->prepare("UPDATE comments SET user_id = NULL WHERE user_id = ?")->execute([$id]);

            // 3. Finally delete the user
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                // Check if user was already deleted or never existed
                $db->rollBack();
                Session::flash('warning', 'El usuario no existe o ya fue eliminado.');
                $this->redirect('/admin/users');
                return;
            }

            $db->commit();

            \Core\SecurityLogger::log('user_deleted_permanently', 'Administrador eliminó permanentemente al usuario ID: ' . $id);
            Session::flash('success', 'Usuario y todo su rastro en el sistema borrados permanentemente.');

        } catch (\Exception $e) {
            if ($db->inTransaction())
                $db->rollBack();
            \Core\SecurityLogger::log('user_delete_failed', 'Error al eliminar usuario ID: ' . $id . '. Error: ' . $e->getMessage(), 'ERROR');
            Session::flash('error', 'No se pudo eliminar: ' . $e->getMessage());
        }

        $this->redirect('/admin/users');
    }

    /**
     * Import users from CSV (Sprint 6.2)
     */
    public function import()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
            $this->viewLayout('admin/users/import', 'admin', [
                'title' => 'Importar Clientes | ' . \Core\Config::get('business.company_name')
            ]);
            return;
        }

        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Error al subir el archivo.');
            $this->redirect('/admin/users/import');
        }

        $handle = fopen($file['tmp_name'], 'r');
        $headers = fgetcsv($handle, 1000, ','); // Skip header

        $db = Database::getInstance()->getConnection();
        $success = 0;
        $errors = [];
        $rowNum = 1;

        $db->beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $rowNum++;
                // Expected format: Name, Email, Company, Phone, Role
                $name    = trim($data[0] ?? '');
                $email   = trim($data[1] ?? '');
                $company = trim($data[2] ?? '');
                $phone   = trim($data[3] ?? '');
                $role    = trim($data[4] ?? 'client');

                if (empty($name) || empty($email)) {
                    $errors[] = "Fila {$rowNum}: Nombre y Email son obligatorios.";
                    continue;
                }

                // Check if email already exists
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors[] = "Fila {$rowNum}: El email '{$email}' ya está registrado.";
                    continue;
                }

                // Create user with random password
                $tempPass = bin2hex(random_bytes(4));
                $hashed = Auth::hashPassword($tempPass);
                
                $sql = "INSERT INTO users (name, email, password, role, company, phone, is_active, tenant_id) VALUES (?, ?, ?, ?, ?, ?, 1, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$name, $email, $hashed, $role, $company, $phone, \Core\Config::get('current_tenant_id', 1)]);
                
                $success++;
                
                // Optional: Send welcome email in background/later. For now, just create.
            }
            $db->commit();
            
            \Core\SecurityLogger::log('users_imported', [
                'count' => $success,
                'errors' => count($errors)
            ]);

            Session::flash('success', "Importación completada: {$success} usuarios creados.");
            if (!empty($errors)) {
                Session::flash('warning', "Se encontraron " . count($errors) . " errores. Revisa el reporte.");
            }

        } catch (\Exception $e) {
            $db->rollBack();
            Session::flash('error', 'Error crítico en la importación: ' . $e->getMessage());
        }

        fclose($handle);
        
        $this->viewLayout('admin/users/import_report', 'admin', [
            'title' => 'Reporte de Importación | ' . \Core\Config::get('business.company_name'),
            'success' => $success,
            'errors' => $errors
        ]);
    }
}

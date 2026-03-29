<?php
require_once __DIR__ . '/public/index.php';

use Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    // ============================================
    // 1. RBAC Dinámico (Permisos Granulares)
    // ============================================
    echo "Creating RBAC tables...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            description VARCHAR(255)
        );
        CREATE TABLE IF NOT EXISTS roles_custom (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            description VARCHAR(255)
        );
        CREATE TABLE IF NOT EXISTS role_permissions (
            role_id INT NOT NULL,
            permission_id INT NOT NULL,
            PRIMARY KEY (role_id, permission_id),
            FOREIGN KEY (role_id) REFERENCES roles_custom(id) ON DELETE CASCADE,
            FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        );
        -- Insert default granular permissions
        INSERT IGNORE INTO permissions (name, description) VALUES 
        ('manage_leads', 'Manage Leads CRM'), 
        ('manage_projects', 'Project Management'), 
        ('manage_finance', 'Invoices and Budgets'), 
        ('manage_services', 'Service Catalog'), 
        ('manage_cms', 'Blog and Pages'), 
        ('view_reports', 'View Dashboard Analytics'), 
        ('manage_users', 'User Administration');
    ");

    // ============================================
    // 2. Auditoría Inmutable (Zero Trust)
    // ============================================
    echo "Upgrading Audit Logs for Immutability...\n";
    $db->exec("
        ALTER TABLE audit_logs 
        ADD COLUMN signature_hash VARCHAR(255) DEFAULT NULL;
    ");

    // Recalculate hash for existing if needed (skipping for simplicity)

    // ============================================
    // 3. User Lead Score
    // ============================================
    echo "Adding Lead Score to Users...\n";
    try {
        $db->exec("ALTER TABLE users ADD COLUMN lead_score INT DEFAULT 0;");
    } catch (\PDOException $e) {
    }

    echo "Phase 4 Migration Complete.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

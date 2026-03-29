-- ============================================
-- Migración Fase 4: Inteligencia y Seguridad
-- Ejecutar en entornos Demo y Production
-- ============================================

-- 1. RBAC Dinámico (Permisos Granulares)
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

-- Insertar permisos granulares base
INSERT IGNORE INTO permissions (name, description) VALUES 
('manage_leads', 'Manage Leads CRM'), 
('manage_projects', 'Project Management'), 
('manage_finance', 'Invoices and Budgets'), 
('manage_services', 'Service Catalog'), 
('manage_cms', 'Blog and Pages'), 
('view_reports', 'View Dashboard Analytics'), 
('manage_users', 'User Administration');

-- 2. Auditoría Inmutable (Zero Trust)
-- Añadir columna para la firma criptográfica (Hash continuo)
ALTER TABLE audit_logs 
ADD COLUMN signature_hash VARCHAR(255) DEFAULT NULL;

-- 3. CRM Lead Scoring
-- Añadir columna de puntuación de leads a la tabla de usuarios
ALTER TABLE users 
ADD COLUMN lead_score INT DEFAULT 0;

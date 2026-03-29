-- Migration: Add Multi-Tenant Support
-- Description: Adds tenant_id to key tables to support multiple organizations.

ALTER TABLE users ADD COLUMN tenant_id INT DEFAULT 1;
ALTER TABLE tickets ADD COLUMN tenant_id INT DEFAULT 1;
ALTER TABLE invoices ADD COLUMN tenant_id INT DEFAULT 1;
ALTER TABLE services ADD COLUMN tenant_id INT DEFAULT 1;
ALTER TABLE service_plans ADD COLUMN tenant_id INT DEFAULT 1;
ALTER TABLE active_services ADD COLUMN tenant_id INT DEFAULT 1;

-- Create tenants table
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed initial tenant
INSERT INTO tenants (id, name, domain) VALUES (1, 'Vezetaelea Internal', 'internal.Vezetaelea.com');

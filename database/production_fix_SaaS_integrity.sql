-- ============================================================================
-- Fix: Integridad Multi-Tenant (SaaS Ready)
-- Agrega tenant_id a tablas que fallaban en Producción
-- ============================================================================

-- Tabla: audit_logs
ALTER TABLE `audit_logs` ADD COLUMN IF NOT EXISTS `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1;
CREATE INDEX IF NOT EXISTS idx_audit_logs_tenant ON audit_logs(tenant_id);

-- Tabla: budgets
ALTER TABLE `budgets` ADD COLUMN IF NOT EXISTS `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1;
CREATE INDEX IF NOT EXISTS idx_budgets_tenant ON budgets(tenant_id);

-- Tabla: chat_messages
ALTER TABLE `chat_messages` ADD COLUMN IF NOT EXISTS `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1;
CREATE INDEX IF NOT EXISTS idx_chat_messages_tenant ON chat_messages(tenant_id);

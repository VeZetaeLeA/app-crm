-- ============================================================
-- SPRINT 2 — Migration: Deliverable Approval System
-- Feature 2.2: Campo status + timestamps de aprobación/rechazo
-- ============================================================

-- Agrega columna status a project_deliverables si no existe
ALTER TABLE `project_deliverables`
    ADD COLUMN IF NOT EXISTS `status` ENUM('pending_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending_review' AFTER `version`,
    ADD COLUMN IF NOT EXISTS `reviewed_by` INT(11) NULL DEFAULT NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `reviewed_at` DATETIME NULL DEFAULT NULL AFTER `reviewed_by`,
    ADD COLUMN IF NOT EXISTS `review_notes` TEXT NULL DEFAULT NULL AFTER `reviewed_at`;

-- Índice para búsquedas rápidas por status
CREATE INDEX IF NOT EXISTS `idx_deliverables_status` ON `project_deliverables` (`status`);

-- Actualizar todos los registros existentes (pending_review por default)
UPDATE `project_deliverables` SET `status` = 'pending_review' WHERE `status` IS NULL;

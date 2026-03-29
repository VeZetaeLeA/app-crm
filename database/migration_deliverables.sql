-- ============================================================================
-- Vezetaelea - MIGRACIÓN: TABLA DE ENTREGABLES (WORKSPACE DE PROYECTO)
-- Fecha: 2026-02-08
-- ============================================================================

USE `Vezetaelea`;

-- ============================================================================
-- TABLA: project_deliverables (Entregables de Proyecto)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `project_deliverables` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `active_service_id` INT UNSIGNED NOT NULL,
    `uploaded_by` INT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `filepath` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) DEFAULT 'other',
    `file_size` BIGINT UNSIGNED DEFAULT 0,
    `version` VARCHAR(20) DEFAULT '1.0',
    `status` ENUM('pending_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending_review',
    `reviewed_by` INT(11) NULL DEFAULT NULL,
    `reviewed_at` DATETIME NULL DEFAULT NULL,
    `review_notes` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_deliverables_service` (`active_service_id`),
    KEY `idx_deliverables_author` (`uploaded_by`),
    KEY `idx_deliverable_status` (`status`),
    CONSTRAINT `fk_deliverables_service` FOREIGN KEY (`active_service_id`) 
        REFERENCES `active_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_deliverables_author` FOREIGN KEY (`uploaded_by`) 
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DE MIGRACIÓN
-- =====================================================

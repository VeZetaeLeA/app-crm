-- ============================================================================
-- GAI Módulo: Sugerencia de Action Items (Soporte Multi-Tenant)
-- ============================================================================

DROP TABLE IF EXISTS `ticket_tasks`;

CREATE TABLE `ticket_tasks` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `description` VARCHAR(255) NOT NULL,
    `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ticket_tasks_ticket` (`ticket_id`),
    KEY `idx_ticket_tasks_tenant` (`tenant_id`),
    CONSTRAINT `fk_ticket_tasks_ticket` FOREIGN KEY (`ticket_id`) 
        REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

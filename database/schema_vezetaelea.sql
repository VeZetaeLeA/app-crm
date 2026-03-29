-- ============================================================================
-- VeZetaeLeA OS — ESQUEMA CANÓNICO DE BASE DE DATOS
-- Versión: 2.0.0 (Post-Rebranding desde DataWyrd)
-- Fecha: 2026-03-18
-- Engine: MySQL 8.0+ / MariaDB 10.6+
-- Encoding: utf8mb4 / utf8mb4_unicode_ci
-- ============================================================================
--
-- HISTORIAL DE VERSIONES:
-- v1.0.0 (2026-02-04) — Esquema inicial (Vezetaelea OS)
-- v1.1.0 (2026-02-08) — Añadida tabla audit_logs
-- v1.2.0 (2026-02-17) — Phase 3: 2FA + sessions (phase3_migration_demo.sql)
-- v1.3.0 (2026-02-20) — Phase 4: RBAC, event sourcing, lead_score
-- v1.4.0 (2026-03-10) — Sprint 1: Versiones de presupuesto (parent_budget_id,
--                         service_reference, budget_number extendido a 30 chars)
-- v2.0.0 (2026-03-18) — Rebranding completo: DataWyrd → VeZetaeLeA.
-- v2.1.0 (2026-03-18) — Sprint 2: Gestión de Proyectos Avanzada:
--                         Añadida tabla project_deliverables con ciclo de vida.
--                         Añadida tabla ticket_tasks (GAI actions).
--
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- CREAR BASE DE DATOS
-- ============================================================================
CREATE DATABASE IF NOT EXISTS `vezetaelea`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    COMMENT 'VeZetaeLeA OS — Base de Datos Principal v2.0';

USE `vezetaelea`;

-- ============================================================================
-- TABLA: users
-- Roles: admin | staff | client
-- Columnas extra (Phase 3): two_factor_secret, two_factor_enabled
-- Columnas extra (Phase 4): lead_score
-- ============================================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`                CHAR(36) NOT NULL,
    `name`                VARCHAR(100) NOT NULL,
    `email`               VARCHAR(150) NOT NULL,
    `phone`               VARCHAR(20) DEFAULT NULL,
    `company`             VARCHAR(100) DEFAULT NULL,
    `password`            VARCHAR(255) NOT NULL,
    `role`                ENUM('admin','staff','client') NOT NULL DEFAULT 'client',
    `avatar`              VARCHAR(255) DEFAULT NULL,
    `is_active`           TINYINT(1) NOT NULL DEFAULT 1,
    `email_verified_at`   TIMESTAMP NULL DEFAULT NULL,
    `remember_token`      VARCHAR(100) DEFAULT NULL,
    -- Phase 3: Two-Factor Authentication
    `two_factor_secret`   VARCHAR(32) DEFAULT NULL COMMENT 'TOTP secret (Google Authenticator compatible)',
    `two_factor_enabled`  TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = 2FA activo',
    -- Phase 4: CRM Lead Scoring
    `lead_score`          INT NOT NULL DEFAULT 0 COMMENT 'Puntuación CRM calculada dinámicamente',
    `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_uuid`  (`uuid`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role`        (`role`),
    KEY `idx_users_active`      (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Usuarios del sistema (admin, staff, client)';

-- ============================================================================
-- TABLA: sessions (Phase 3)
-- Almacenamiento de sesiones en BD con metadatos de seguridad.
-- ============================================================================
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
    `id`            VARCHAR(128) NOT NULL COMMENT 'Session ID único',
    `payload`       TEXT NOT NULL COMMENT 'Datos serializados (base64)',
    `last_activity` INT UNSIGNED NOT NULL COMMENT 'Unix timestamp de última actividad',
    `user_id`       INT UNSIGNED DEFAULT NULL COMMENT 'FK a users.id (NULL = guest)',
    `ip_address`    VARCHAR(45) DEFAULT NULL,
    `user_agent`    TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_sessions_last_activity` (`last_activity`),
    KEY `idx_sessions_user`          (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Sesiones web con metadatos de seguridad';

-- ============================================================================
-- TABLA: service_categories
-- ============================================================================
DROP TABLE IF EXISTS `service_categories`;
CREATE TABLE `service_categories` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(100) NOT NULL,
    `slug`           VARCHAR(100) NOT NULL,
    `description`    TEXT DEFAULT NULL,
    `icon`           VARCHAR(50) DEFAULT NULL COMMENT 'Nombre de Material Icon',
    `order_position` INT NOT NULL DEFAULT 0,
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_service_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: services
-- ============================================================================
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id`       INT UNSIGNED NOT NULL,
    `name`              VARCHAR(150) NOT NULL,
    `slug`              VARCHAR(150) NOT NULL,
    `short_description` VARCHAR(255) DEFAULT NULL,
    `full_description`  TEXT DEFAULT NULL,
    `icon`              VARCHAR(50) DEFAULT NULL,
    `image`             VARCHAR(255) DEFAULT NULL,
    `is_featured`       TINYINT(1) NOT NULL DEFAULT 0,
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `order_position`    INT NOT NULL DEFAULT 0,
    `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_services_slug` (`slug`),
    KEY `idx_services_category` (`category_id`),
    CONSTRAINT `fk_services_category` FOREIGN KEY (`category_id`)
        REFERENCES `service_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: service_plans
-- ============================================================================
DROP TABLE IF EXISTS `service_plans`;
CREATE TABLE `service_plans` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id`  INT UNSIGNED NOT NULL,
    `name`        VARCHAR(50) NOT NULL,
    `level`       ENUM('basic','medium','advanced') NOT NULL DEFAULT 'basic',
    `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency`    CHAR(3) NOT NULL DEFAULT 'USD',
    `features`    JSON DEFAULT NULL COMMENT 'Array JSON de características',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_service_plans_service` (`service_id`),
    CONSTRAINT `fk_service_plans_service` FOREIGN KEY (`service_id`)
        REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: tickets
-- Estados del flujo comercial completo.
-- ============================================================================
DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_number`   VARCHAR(20) NOT NULL COMMENT 'Formato: TKT-XXXXXX',
    `client_id`       INT UNSIGNED NOT NULL,
    `assigned_to`     INT UNSIGNED DEFAULT NULL,
    `service_plan_id` INT UNSIGNED NOT NULL,
    `subject`         VARCHAR(200) NOT NULL,
    `description`     TEXT NOT NULL,
    `priority`        ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
    `status`          ENUM(
                          'open','in_analysis','budget_sent','budget_approved',
                          'budget_rejected','invoiced','payment_pending','active','closed'
                      ) NOT NULL DEFAULT 'open',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `closed_at`       TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tickets_number`      (`ticket_number`),
    KEY `idx_tickets_client`            (`client_id`),
    KEY `idx_tickets_assigned`          (`assigned_to`),
    KEY `idx_tickets_status`            (`status`),
    KEY `idx_tickets_service_plan`      (`service_plan_id`),
    CONSTRAINT `fk_tickets_client`      FOREIGN KEY (`client_id`)      REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_tickets_assigned`    FOREIGN KEY (`assigned_to`)    REFERENCES `users` (`id`) ON DELETE SET NULL  ON UPDATE CASCADE,
    CONSTRAINT `fk_tickets_service_plan`FOREIGN KEY (`service_plan_id`)REFERENCES `service_plans` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: ticket_tasks (GAI-02: Action Items extraídos por IA — Sprint 2)
-- ============================================================================
DROP TABLE IF EXISTS `ticket_tasks`;
CREATE TABLE `ticket_tasks` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id`   INT UNSIGNED NOT NULL,
    `tenant_id`   INT UNSIGNED NOT NULL DEFAULT 1,
    `description` TEXT NOT NULL,
    `is_done`     TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ticket_tasks_ticket` (`ticket_id`),
    CONSTRAINT `fk_ticket_tasks_ticket` FOREIGN KEY (`ticket_id`)
        REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tareas sugeridas por el Copilot GAI al crear un ticket';

-- ============================================================================
-- TABLA: chat_messages
-- ============================================================================
DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id`       INT UNSIGNED NOT NULL,
    `user_id`         INT UNSIGNED NOT NULL COMMENT '0 = mensaje de sistema (GAI)',
    `message`         TEXT NOT NULL,
    `message_type`    ENUM('text','file','system') NOT NULL DEFAULT 'text',
    `attachment_path` VARCHAR(255) DEFAULT NULL,
    `is_read`         TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_chat_ticket_created` (`ticket_id`, `created_at`),
    KEY `idx_chat_user`           (`user_id`),
    CONSTRAINT `fk_chat_ticket` FOREIGN KEY (`ticket_id`)
        REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: ticket_attachments
-- ============================================================================
DROP TABLE IF EXISTS `ticket_attachments`;
CREATE TABLE `ticket_attachments` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id`  INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `filename`   VARCHAR(255) NOT NULL,
    `filepath`   VARCHAR(255) NOT NULL,
    `filetype`   VARCHAR(50) NOT NULL,
    `filesize`   INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_attachments_ticket` (`ticket_id`),
    CONSTRAINT `fk_attachments_ticket` FOREIGN KEY (`ticket_id`)
        REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_attachments_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: budgets — v1.4.0+
-- Campos v1.4: parent_budget_id (versionado), service_reference
-- Número de presupuesto: VZL-B{AÑO}-{HEX4}  (antes DW-B...)
-- ============================================================================
DROP TABLE IF EXISTS `budgets`;
CREATE TABLE `budgets` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `budget_number`    VARCHAR(30) NOT NULL COMMENT 'Formato: VZL-B{AÑO}-{HEX4}',
    `ticket_id`        INT UNSIGNED NOT NULL,
    `parent_budget_id` INT UNSIGNED DEFAULT NULL COMMENT 'FK a budgets.id para versionado',
    `version`          INT NOT NULL DEFAULT 1,
    `title`            VARCHAR(200) NOT NULL,
    `service_reference`VARCHAR(255) DEFAULT NULL COMMENT 'Nombre/descripción corta del servicio',
    `scope`            TEXT DEFAULT NULL,
    `timeline_weeks`   INT DEFAULT NULL,
    `subtotal`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `tax_rate`         DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `tax_amount`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total`            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `currency`         CHAR(3) NOT NULL DEFAULT 'USD',
    `valid_days`       INT NOT NULL DEFAULT 30,
    `status`           ENUM('draft','sent','approved','rejected') NOT NULL DEFAULT 'draft',
    `notes`            TEXT DEFAULT NULL,
    `approved_at`      TIMESTAMP NULL DEFAULT NULL,
    `created_by`       INT UNSIGNED NOT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_budgets_number`          (`budget_number`),
    KEY `idx_budgets_ticket`                (`ticket_id`),
    KEY `idx_budgets_status`                (`status`),
    KEY `idx_budgets_parent`                (`parent_budget_id`),
    CONSTRAINT `fk_budgets_ticket`          FOREIGN KEY (`ticket_id`)        REFERENCES `tickets` (`id`)  ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_budgets_parent`          FOREIGN KEY (`parent_budget_id`) REFERENCES `budgets` (`id`)  ON DELETE SET NULL  ON UPDATE CASCADE,
    CONSTRAINT `fk_budgets_created_by`      FOREIGN KEY (`created_by`)       REFERENCES `users` (`id`)    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Presupuestos con soporte de versionado';

-- ============================================================================
-- TABLA: budget_items
-- ============================================================================
DROP TABLE IF EXISTS `budget_items`;
CREATE TABLE `budget_items` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `budget_id`      INT UNSIGNED NOT NULL,
    `description`    VARCHAR(255) NOT NULL,
    `type`           ENUM('service','license','infrastructure','other') NOT NULL DEFAULT 'service',
    `quantity`       DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    `unit_price`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `order_position` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_budget_items_budget` (`budget_id`),
    CONSTRAINT `fk_budget_items_budget` FOREIGN KEY (`budget_id`)
        REFERENCES `budgets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: invoices — v2.0+
-- Número de factura: VZL-INV-{YYYYMMDD}-{HEX4}  (antes DW-INV-...)
-- Columna extra (MP Integration): mp_preference_id
-- ============================================================================
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_number`   VARCHAR(35) NOT NULL COMMENT 'Formato: VZL-INV-{YYYYMMDD}-{HEX4}',
    `budget_id`        INT UNSIGNED NOT NULL,
    `client_id`        INT UNSIGNED NOT NULL,
    `service_reference`VARCHAR(255) DEFAULT NULL COMMENT 'Referencia del servicio (copiada del presupuesto)',
    `issue_date`       DATE NOT NULL,
    `due_date`         DATE NOT NULL,
    `subtotal`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `tax_rate`         DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `tax_amount`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total`            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `paid_amount`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `currency`         CHAR(3) NOT NULL DEFAULT 'USD',
    `status`           ENUM('draft','unpaid','processing','partial','paid','overdue','void') NOT NULL DEFAULT 'unpaid',
    `mp_preference_id` VARCHAR(255) DEFAULT NULL COMMENT 'ID de preferencia MercadoPago',
    `paid_at`          TIMESTAMP NULL DEFAULT NULL,
    `notes`            TEXT DEFAULT NULL,
    `created_by`       INT UNSIGNED NOT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_invoices_number`     (`invoice_number`),
    KEY `idx_invoices_client`           (`client_id`),
    KEY `idx_invoices_status`           (`status`),
    KEY `idx_invoices_budget`           (`budget_id`),
    CONSTRAINT `fk_invoices_budget`     FOREIGN KEY (`budget_id`)   REFERENCES `budgets` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_invoices_client`     FOREIGN KEY (`client_id`)   REFERENCES `users` (`id`)   ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_invoices_created_by` FOREIGN KEY (`created_by`)  REFERENCES `users` (`id`)   ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Facturas electrónicas con soporte MercadoPago y Event Sourcing';

-- ============================================================================
-- TABLA: invoice_events (Event Sourcing — Sprint 4 / E11-012)
-- Registra TODO cambio de estado financiero. La suma de eventos = balance real.
-- ============================================================================
DROP TABLE IF EXISTS `invoice_events`;
CREATE TABLE `invoice_events` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_id`  INT UNSIGNED NOT NULL,
    `event_type`  VARCHAR(50) NOT NULL COMMENT 'CREATE | APPLY_PAYMENT | VOID | DISCOUNT | REFUND',
    `amount`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payload`     JSON DEFAULT NULL COMMENT 'Datos adicionales del evento',
    `created_by`  INT UNSIGNED DEFAULT NULL,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_invoice_events_invoice` (`invoice_id`),
    KEY `idx_invoice_events_type`    (`event_type`),
    KEY `idx_invoice_events_created` (`created_at`),
    CONSTRAINT `fk_invoice_events_invoice` FOREIGN KEY (`invoice_id`)
        REFERENCES `invoices` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Event Store inmutable para contabilidad de facturas (E11-012)';

-- ============================================================================
-- TABLA: payment_receipts
-- ============================================================================
DROP TABLE IF EXISTS `payment_receipts`;
CREATE TABLE `payment_receipts` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_id`     INT UNSIGNED NOT NULL,
    `uploaded_by`    INT UNSIGNED NOT NULL,
    `filename`       VARCHAR(255) NOT NULL,
    `filepath`       VARCHAR(255) NOT NULL,
    `amount`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payment_date`   DATE NOT NULL,
    `payment_method` VARCHAR(50) DEFAULT NULL,
    `status`         ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    `verified_by`    INT UNSIGNED DEFAULT NULL,
    `verified_at`    TIMESTAMP NULL DEFAULT NULL,
    `notes`          TEXT DEFAULT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_receipts_invoice` (`invoice_id`),
    CONSTRAINT `fk_receipts_invoice`      FOREIGN KEY (`invoice_id`)  REFERENCES `invoices` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_receipts_uploaded_by`  FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)    ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_receipts_verified_by`  FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)    ON DELETE SET NULL  ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: active_services
-- ============================================================================
DROP TABLE IF EXISTS `active_services`;
CREATE TABLE `active_services` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id`       INT UNSIGNED NOT NULL,
    `ticket_id`       INT UNSIGNED NOT NULL,
    `invoice_id`      INT UNSIGNED NOT NULL,
    `service_plan_id` INT UNSIGNED NOT NULL,
    `name`            VARCHAR(200) NOT NULL,
    `description`     TEXT DEFAULT NULL,
    `status`          ENUM('active','suspended','cancelled','completed') NOT NULL DEFAULT 'active',
    `start_date`      DATE NOT NULL,
    `end_date`        DATE DEFAULT NULL,
    `renewal_date`    DATE DEFAULT NULL,
    `activated_by`    INT UNSIGNED NOT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_active_services_client` (`client_id`),
    KEY `idx_active_services_status` (`status`),
    CONSTRAINT `fk_active_services_client`       FOREIGN KEY (`client_id`)       REFERENCES `users` (`id`)          ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_active_services_ticket`       FOREIGN KEY (`ticket_id`)       REFERENCES `tickets` (`id`)        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_active_services_invoice`      FOREIGN KEY (`invoice_id`)      REFERENCES `invoices` (`id`)       ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_active_services_plan`         FOREIGN KEY (`service_plan_id`) REFERENCES `service_plans` (`id`)  ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_active_services_activated_by` FOREIGN KEY (`activated_by`)    REFERENCES `users` (`id`)          ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: project_deliverables (Sprint 2 — Gestión de Proyectos Avanzada)
-- ============================================================================
DROP TABLE IF EXISTS `project_deliverables`;
CREATE TABLE `project_deliverables` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `active_service_id` INT UNSIGNED NOT NULL,
    `uploaded_by`       INT UNSIGNED NOT NULL,
    `title`             VARCHAR(200) NOT NULL,
    `description`       TEXT DEFAULT NULL,
    `filename`          VARCHAR(255) NOT NULL,
    `filepath`          VARCHAR(255) NOT NULL,
    `file_type`         VARCHAR(50) DEFAULT 'other',
    `file_size`         BIGINT UNSIGNED DEFAULT 0,
    `version`           VARCHAR(20) DEFAULT '1.0',
    `status`            ENUM('pending_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending_review',
    `reviewed_by`       INT(11) NULL DEFAULT NULL,
    `reviewed_at`       DATETIME NULL DEFAULT NULL,
    `review_notes`      TEXT NULL DEFAULT NULL,
    `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_deliverables_service` (`active_service_id`),
    KEY `idx_deliverables_author`  (`uploaded_by`),
    KEY `idx_deliverable_status`   (`status`),
    CONSTRAINT `fk_deliverables_service` FOREIGN KEY (`active_service_id`) 
        REFERENCES `active_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_deliverables_author` FOREIGN KEY (`uploaded_by`) 
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Entregables de proyecto con ciclo de revisión y aprobación del cliente';

-- ============================================================================
-- TABLA: blog_categories
-- ============================================================================
DROP TABLE IF EXISTS `blog_categories`;
CREATE TABLE `blog_categories` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `color`       VARCHAR(7) DEFAULT '#3B82F6',
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_blog_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: blog_posts
-- ============================================================================
DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `author_id`        INT UNSIGNED NOT NULL,
    `category_id`      INT UNSIGNED NOT NULL,
    `title`            VARCHAR(255) NOT NULL,
    `slug`             VARCHAR(255) NOT NULL,
    `excerpt`          VARCHAR(500) DEFAULT NULL,
    `content`          LONGTEXT NOT NULL,
    `featured_image`   VARCHAR(255) DEFAULT NULL,
    `status`           ENUM('draft','scheduled','published') NOT NULL DEFAULT 'draft',
    `published_at`     TIMESTAMP NULL DEFAULT NULL,
    `views_count`      INT UNSIGNED NOT NULL DEFAULT 0,
    `allow_comments`   TINYINT(1) NOT NULL DEFAULT 1,
    `meta_title`       VARCHAR(100) DEFAULT NULL,
    `meta_description` VARCHAR(255) DEFAULT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_blog_posts_slug`          (`slug`),
    KEY `idx_blog_posts_status_date`         (`status`, `published_at`),
    KEY `idx_blog_posts_author`              (`author_id`),
    KEY `idx_blog_posts_category`            (`category_id`),
    CONSTRAINT `fk_blog_posts_author`        FOREIGN KEY (`author_id`)   REFERENCES `users` (`id`)           ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_blog_posts_category`      FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: notifications
-- ============================================================================
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `type`       VARCHAR(50) NOT NULL,
    `title`      VARCHAR(200) NOT NULL,
    `message`    TEXT NOT NULL,
    `link`       VARCHAR(255) DEFAULT NULL,
    `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
    `email_sent` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_user_read` (`user_id`, `is_read`),
    KEY `idx_notifications_created`   (`created_at`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: email_logs
-- ============================================================================
DROP TABLE IF EXISTS `email_logs`;
CREATE TABLE `email_logs` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `to_email`      VARCHAR(150) NOT NULL,
    `to_name`       VARCHAR(100) DEFAULT NULL,
    `subject`       VARCHAR(255) NOT NULL,
    `body`          TEXT NOT NULL,
    `status`        ENUM('sent','failed') NOT NULL DEFAULT 'sent',
    `error_message` TEXT DEFAULT NULL,
    `related_type`  VARCHAR(50) DEFAULT NULL COMMENT 'ticket | invoice | budget',
    `related_id`    INT UNSIGNED DEFAULT NULL,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email_logs_created` (`created_at`),
    KEY `idx_email_logs_related` (`related_type`, `related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: audit_logs (v1.1.0) + columna signature_hash (Phase 4)
-- ============================================================================
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`         INT UNSIGNED DEFAULT NULL COMMENT 'NULL = acción de invitado',
    `user_email`      VARCHAR(255) NOT NULL,
    `user_role`       VARCHAR(50) NOT NULL,
    `action`          VARCHAR(100) NOT NULL,
    `details`         TEXT DEFAULT NULL COMMENT 'JSON con detalles adicionales',
    `level`           ENUM('INFO','WARN','ERROR') NOT NULL DEFAULT 'INFO',
    `ip_address`      VARCHAR(45) NOT NULL,
    `user_agent`      VARCHAR(255) DEFAULT NULL,
    `request_uri`     VARCHAR(255) DEFAULT NULL,
    `request_method`  VARCHAR(10) DEFAULT NULL,
    `signature_hash`  VARCHAR(255) DEFAULT NULL COMMENT 'Hash criptográfico para inmutabilidad (Phase 4)',
    `request_id`      VARCHAR(36) DEFAULT NULL COMMENT 'UUID de correlación de la petición HTTP',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_user_id`    (`user_id`),
    KEY `idx_audit_action`     (`action`),
    KEY `idx_audit_level`      (`level`),
    KEY `idx_audit_created_at` (`created_at`),
    KEY `idx_audit_user_email` (`user_email`),
    KEY `idx_audit_composite`  (`user_id`, `action`, `created_at`),
    KEY `idx_audit_date_range` (`created_at`, `action`),
    KEY `idx_audit_user_date`  (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Auditoría inmutable de acciones críticas (Zero Trust)';

-- ============================================================================
-- TABLA: jobs (Cola Asíncrona — queue_and_mp_migration)
-- ============================================================================
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `job_class`     VARCHAR(255) NOT NULL,
    `payload`       JSON NOT NULL,
    `attempts`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status`        ENUM('pending','processing','failed') NOT NULL DEFAULT 'pending',
    `error_message` TEXT DEFAULT NULL,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_jobs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cola de trabajos asincrónicos (emails, webhooks, etc.)';

-- ============================================================================
-- TABLA: instagram_calendar (Módulo Instagram Copilot)
-- ============================================================================
DROP TABLE IF EXISTS `instagram_calendar`;
CREATE TABLE `instagram_calendar` (
    `id`         INT NOT NULL AUTO_INCREMENT,
    `week_label` VARCHAR(255) NOT NULL,
    `start_date` DATE NOT NULL,
    `status`     VARCHAR(50) DEFAULT 'draft',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: instagram_posts
-- ============================================================================
DROP TABLE IF EXISTS `instagram_posts`;
CREATE TABLE `instagram_posts` (
    `id`              INT NOT NULL AUTO_INCREMENT,
    `calendar_id`     INT NOT NULL,
    `day_of_week`     VARCHAR(20) NOT NULL,
    `publish_date`    DATE NOT NULL,
    `publish_time`    TIME NOT NULL,
    `strategic_pilar` VARCHAR(100) NOT NULL,
    `post_format`     VARCHAR(50) NOT NULL,
    `internal_title`  VARCHAR(255) NOT NULL,
    `copy_text`       TEXT NOT NULL,
    `cta_text`        VARCHAR(255) NOT NULL,
    `hashtags`        TEXT NOT NULL,
    `visual_prompt`   TEXT NOT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `calendar_id` (`calendar_id`),
    CONSTRAINT `fk_instagram_posts_calendar` FOREIGN KEY (`calendar_id`)
        REFERENCES `instagram_calendar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: permissions (Phase 4 — RBAC Granular)
-- ============================================================================
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: roles_custom (Phase 4 — RBAC Granular)
-- ============================================================================
DROP TABLE IF EXISTS `roles_custom`;
CREATE TABLE `roles_custom` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: role_permissions (Phase 4 — RBAC Granular)
-- ============================================================================
CREATE TABLE `role_permissions` (
    `role_id`       INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role`       FOREIGN KEY (`role_id`)       REFERENCES `roles_custom` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ÍNDICES DE OPTIMIZACIÓN
-- ============================================================================
-- Índice para Performance Dashboard: tickets recientes por status
CREATE INDEX IF NOT EXISTS `idx_tickets_status_created` ON `tickets` (`status`, `created_at`);
-- Índice para consultas financieras por rango de fecha
CREATE INDEX IF NOT EXISTS `idx_invoices_created_status` ON `invoices` (`created_at`, `status`);
-- Índice para FinOps: eventos de facturación por fecha
CREATE INDEX IF NOT EXISTS `idx_inv_events_date` ON `invoice_events` (`created_at`);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- FIN DEL ESQUEMA — VeZetaeLeA OS v2.0.0
-- ============================================================================

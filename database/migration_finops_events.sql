-- ============================================================================
-- E11-012: Evolución Schema DB a Event Sourcing (FinOps)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `invoice_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `event_type` ENUM('CREATE', 'APPLY_PAYMENT', 'VOID', 'DISCOUNT', 'REFUND') NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payload` JSON DEFAULT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_invoice_events_invoice` (`invoice_id`),
    KEY `idx_invoice_events_tenant` (`tenant_id`),
    KEY `idx_invoice_events_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migración de datos iniciales (proteger integridad de facturas existentes)
-- Creamos un evento 'CREATE' para cada factura existente
INSERT INTO `invoice_events` (invoice_id, tenant_id, event_type, amount, payload, created_by, created_at)
SELECT id, tenant_id, 'CREATE', total, JSON_OBJECT('initial_total', total), created_by, created_at FROM invoices;

-- Agregamos un evento 'APPLY_PAYMENT' para las que ya tienen pagos registrados
INSERT INTO `invoice_events` (invoice_id, tenant_id, event_type, amount, payload, created_by, created_at)
SELECT id, tenant_id, 'APPLY_PAYMENT', paid_amount, JSON_OBJECT('legacy_payment', true), created_by, updated_at 
FROM invoices WHERE paid_amount > 0;

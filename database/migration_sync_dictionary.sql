-- =====================================================
-- Migration: Sincronización Diccionario de Datos vs Código (Fase 3)
-- Descripción: Ajustes de ENUMs y consistencia de tablas
-- Fecha: 2026-02-08
-- =====================================================

-- 1. Actualizar estados de Facturas (invoices) para incluir 'cancelled'
ALTER TABLE `invoices` 
MODIFY COLUMN `status` ENUM('unpaid', 'processing', 'paid', 'overdue', 'cancelled') 
NOT NULL DEFAULT 'unpaid';

-- 2. Sincronizar estados de Servicios Activos (active_services) con la lógica de Gestión de Proyectos
-- Esto permite que la BD soporte el flujo avanzado del código
ALTER TABLE `active_services` 
MODIFY COLUMN `status` ENUM('pending', 'in_progress', 'on_hold', 'completed', 'cancelled') 
NOT NULL DEFAULT 'pending';

-- 3. Asegurar consistencia en tipos de datos de auditoría (user_id debe coincidir con users.id)
ALTER TABLE `audit_logs` 
MODIFY COLUMN `user_id` int(10) UNSIGNED DEFAULT NULL;

-- 4. Actualizar comentarios de tabla para mayor claridad documental
ALTER TABLE `active_services` COMMENT = 'Representa proyectos o servicios en ejecución (antiguo projects)';

-- =====================================================
-- Verificación de Integridad Referencial
-- =====================================================

-- Asegurar que los entregables apunten correctamente a active_services
-- (Ya está en el diccionario, pero reforzamos la validación)
-- ALTER TABLE `project_deliverables` ADD CONSTRAINT `fk_deliverables_service` 
-- FOREIGN KEY (`active_service_id`) REFERENCES `active_services`(`id`) ON DELETE CASCADE;

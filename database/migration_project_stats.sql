-- ============================================================================
-- Vezetaelea - MIGRACIÓN: ESTADÍSTICAS DE PROYECTO (PRD v1.0)
-- Fecha: 2026-02-17
-- ============================================================================

USE `Vezetaelea`;

-- ============================================================================
-- Agregar columna total_deliverables a active_services
-- ============================================================================
ALTER TABLE `active_services` 
ADD COLUMN `total_deliverables` INT UNSIGNED DEFAULT 0 AFTER `activated_by`;

-- =====================================================
-- FIN DE MIGRACIÓN
-- =====================================================

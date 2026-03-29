-- =====================================================
-- Vezetaelea - OPTIMIZACIÓN DE RENDIMIENTO
-- Descripción: Creación de índices para acelerar Dashboard y CRM
-- Fecha: 2026-03-01
-- =====================================================

-- 1. Índices para Gráficos de Rendimiento y Estadísticas Temporales
-- Acelera los conteos por fecha en el Dashboard Admin y Staff
ALTER TABLE `tickets` ADD INDEX IF NOT EXISTS `idx_tickets_created_at` (`created_at`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_created_at` (`created_at`);
ALTER TABLE `active_services` ADD INDEX IF NOT EXISTS `idx_active_services_created_at` (`created_at`);

-- 2. Índices para Búsquedas de Inteligencia de Clientes (CRM)
-- Acelera la identificación de leads por empresa
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_company` (`company`);

-- 3. Índice para Sesiones de Usuario
-- Optimiza el Garbage Collector de sesiones
ALTER TABLE `sessions` ADD INDEX IF NOT EXISTS `idx_sessions_expiry` (`last_activity`);

-- 4. Índice para Logs de Email
-- Acelera la vista de historial de envíos
ALTER TABLE `email_logs` ADD INDEX IF NOT EXISTS `idx_email_logs_status` (`status`);

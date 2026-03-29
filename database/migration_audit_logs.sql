-- =====================================================
-- Migration: Audit Logs Table
-- Descripción: Tabla para auditoría y logging de acciones críticas
-- Fecha: 2026-02-08
-- =====================================================

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID del usuario que realizó la acción (NULL para guests)',
  `user_email` varchar(255) NOT NULL COMMENT 'Email del usuario o "guest"',
  `user_role` varchar(50) NOT NULL COMMENT 'Rol del usuario',
  `action` varchar(100) NOT NULL COMMENT 'Nombre de la acción realizada',
  `details` text DEFAULT NULL COMMENT 'Detalles adicionales en JSON',
  `level` enum('INFO','WARN','ERROR') NOT NULL DEFAULT 'INFO' COMMENT 'Nivel de log',
  `ip_address` varchar(45) NOT NULL COMMENT 'Dirección IP del usuario',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'User agent del navegador',
  `request_uri` varchar(255) DEFAULT NULL COMMENT 'URI de la petición',
  `request_method` varchar(10) DEFAULT NULL COMMENT 'Método HTTP (GET, POST, etc.)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_level` (`level`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_email` (`user_email`),
  KEY `idx_composite` (`user_id`, `action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de auditoría de acciones críticas';

-- =====================================================
-- Índices adicionales para optimización de queries
-- =====================================================

-- Índice para búsquedas por rango de fechas
CREATE INDEX idx_date_range ON audit_logs(created_at, action);

-- Índice para búsquedas por usuario y fecha
CREATE INDEX idx_user_date ON audit_logs(user_id, created_at);

-- =====================================================
-- Comentarios de acciones comunes
-- =====================================================

-- Acciones de autenticación:
-- - login_success: Login exitoso
-- - login_failed: Intento de login fallido
-- - logout: Cierre de sesión
-- - password_changed: Cambio de contraseña

-- Acciones de usuarios:
-- - user_created: Usuario creado
-- - user_updated: Usuario actualizado
-- - user_deleted: Usuario eliminado
-- - user_activated: Usuario activado
-- - user_deactivated: Usuario desactivado

-- Acciones de tickets:
-- - ticket_created: Ticket creado
-- - ticket_updated: Ticket actualizado
-- - ticket_status_changed: Estado de ticket cambiado
-- - ticket_assigned: Ticket asignado a staff
-- - ticket_closed: Ticket cerrado

-- Acciones de presupuestos:
-- - budget_created: Presupuesto creado
-- - budget_sent: Presupuesto enviado al cliente
-- - budget_approved: Presupuesto aprobado
-- - budget_rejected: Presupuesto rechazado

-- Acciones de facturas:
-- - invoice_created: Factura creada
-- - invoice_sent: Factura enviada
-- - invoice_paid: Factura pagada
-- - invoice_cancelled: Factura cancelada

-- Acciones de proyectos:
-- - project_created: Proyecto creado
-- - project_status_changed: Estado de proyecto cambiado
-- - project_file_uploaded: Archivo subido al proyecto
-- - project_file_deleted: Archivo eliminado del proyecto

-- Acciones de seguridad:
-- - access_denied: Acceso denegado a recurso
-- - unauthorized_access: Intento de acceso no autorizado
-- - suspicious_activity: Actividad sospechosa detectada

-- Acciones de sistema:
-- - application_error: Error de aplicación
-- - database_error: Error de base de datos
-- - email_sent: Email enviado
-- - email_failed: Fallo al enviar email

-- =====================================================
-- Ejemplo de consultas útiles
-- =====================================================

-- Obtener todos los logins del día:
-- SELECT * FROM audit_logs WHERE action = 'login_success' AND DATE(created_at) = CURDATE();

-- Obtener intentos fallidos de login:
-- SELECT * FROM audit_logs WHERE action = 'login_failed' ORDER BY created_at DESC LIMIT 50;

-- Obtener actividad de un usuario específico:
-- SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC;

-- Obtener errores recientes:
-- SELECT * FROM audit_logs WHERE level = 'ERROR' ORDER BY created_at DESC LIMIT 100;

-- Estadísticas de acciones por día:
-- SELECT DATE(created_at) as date, action, COUNT(*) as count 
-- FROM audit_logs 
-- GROUP BY DATE(created_at), action 
-- ORDER BY date DESC, count DESC;

-- =====================================================
-- Política de retención (opcional)
-- =====================================================

-- Crear evento para limpiar logs antiguos (más de 1 año)
-- DELIMITER $$
-- CREATE EVENT IF NOT EXISTS cleanup_old_audit_logs
-- ON SCHEDULE EVERY 1 MONTH
-- DO BEGIN
--   DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
-- END$$
-- DELIMITER ;

-- =====================================================
-- Vezetaelea - PHASE 3 MIGRATION SCRIPT
-- Enterprise Security Features
-- =====================================================
-- Entorno: DEMO/QA
-- Fecha: 2026-02-17
-- Descripción: Script consolidado para migrar la base
--              de datos del entorno demo con las nuevas
--              funcionalidades de seguridad empresarial.
-- =====================================================

-- =====================================================
-- 1. AUTENTICACIÓN DE DOS FACTORES (2FA)
-- =====================================================
-- Agregar columnas para soporte de 2FA en la tabla users

-- Verificar si las columnas ya existen antes de agregarlas
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname1 = 'two_factor_secret';
SET @columnname2 = 'two_factor_enabled';

-- Agregar two_factor_secret si no existe
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE 
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname1
  ) > 0,
  'SELECT 1', -- Si existe, no hacer nada
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN two_factor_secret VARCHAR(32) DEFAULT NULL AFTER password') -- Si no existe, agregar
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar two_factor_enabled si no existe
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE 
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname2
  ) > 0,
  'SELECT 1', -- Si existe, no hacer nada
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0 AFTER two_factor_secret') -- Si no existe, agregar
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- 2. SESIONES EN BASE DE DATOS
-- =====================================================
-- Crear tabla para almacenar sesiones con metadatos de seguridad

CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(128) NOT NULL COMMENT 'Session ID único',
    `payload` TEXT NOT NULL COMMENT 'Datos de sesión serializados (base64)',
    `last_activity` INT UNSIGNED NOT NULL COMMENT 'Timestamp de última actividad',
    `user_id` INT UNSIGNED DEFAULT NULL COMMENT 'ID del usuario (si está autenticado)',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Dirección IP del cliente',
    `user_agent` TEXT DEFAULT NULL COMMENT 'User-Agent del navegador',
    PRIMARY KEY (`id`),
    KEY `idx_sessions_last_activity` (`last_activity`) COMMENT 'Índice para garbage collection',
    KEY `idx_sessions_user` (`user_id`) COMMENT 'Índice para consultas por usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Almacenamiento de sesiones con metadatos de seguridad';

-- =====================================================
-- VERIFICACIÓN DE CAMBIOS
-- =====================================================
-- Consultas para verificar que todo se aplicó correctamente

SELECT 
    'users - two_factor_secret' AS verificacion,
    IF(COUNT(*) > 0, '✓ Columna existe', '✗ ERROR: Columna no encontrada') AS estado
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'two_factor_secret'

UNION ALL

SELECT 
    'users - two_factor_enabled' AS verificacion,
    IF(COUNT(*) > 0, '✓ Columna existe', '✗ ERROR: Columna no encontrada') AS estado
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'two_factor_enabled'

UNION ALL

SELECT 
    'sessions - tabla' AS verificacion,
    IF(COUNT(*) > 0, '✓ Tabla existe', '✗ ERROR: Tabla no encontrada') AS estado
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'sessions';

-- =====================================================
-- NOTAS POST-MIGRACIÓN
-- =====================================================
-- 
-- ✅ FUNCIONALIDADES HABILITADAS:
--
-- 1. Autenticación de Dos Factores (2FA)
--    - Los usuarios ahora pueden activar 2FA desde /profile/settings
--    - Compatible con Google Authenticator, Authy, Microsoft Authenticator
--
-- 2. Sesiones en Base de Datos
--    - Las sesiones ahora se almacenan en la tabla 'sessions'
--    - Incluye metadatos de seguridad (IP, User-Agent)
--    - Preparado para escalabilidad multi-servidor
--
-- 3. Audit Logs
--    - Eventos de 2FA se registran en 'audit_logs'
--    - Visible desde /admin/logs (solo administradores)
--
-- ⚠️ IMPORTANTE:
--    - No se requiere reinicio de servicios
--    - La aplicación detectará automáticamente las nuevas características
--    - Los usuarios existentes tienen 2FA desactivado por defecto
--    - Las sesiones antiguas migrarán automáticamente
--
-- 📋 PRÓXIMOS PASOS:
--    1. Ejecutar este script en tu base de datos demo
--    2. Verificar el resultado de las consultas de verificación
--    3. Sincronizar el código de la aplicación (git pull/deploy)
--    4. Probar el login y la configuración de 2FA
--
-- =====================================================

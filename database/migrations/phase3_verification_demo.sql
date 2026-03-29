-- =====================================================
-- Vezetaelea - PHASE 3 VERIFICATION SCRIPT
-- =====================================================
-- Entorno: DEMO/QA
-- Fecha: 2026-02-17
-- Propósito: Verificar que todos los componentes de
--            Phase 3 están correctamente implementados
-- =====================================================

-- =====================================================
-- PARTE 1: VERIFICACIÓN DE ESTRUCTURA
-- =====================================================

SELECT '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep;
SELECT 'VERIFICACIÓN DE ESTRUCTURA - PHASE 3' AS mensaje;
SELECT '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep;

-- Verificar columnas 2FA en tabla users
SELECT 
    'users.two_factor_secret' AS elemento,
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ EXISTE'
        ELSE '✗ FALTA - Ejecutar migración'
    END AS estado,
    COLUMN_TYPE AS tipo,
    IS_NULLABLE AS nulo,
    COLUMN_DEFAULT AS valor_default
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'two_factor_secret';

SELECT 
    'users.two_factor_enabled' AS elemento,
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ EXISTE'
        ELSE '✗ FALTA - Ejecutar migración'
    END AS estado,
    COLUMN_TYPE AS tipo,
    IS_NULLABLE AS nulo,
    COLUMN_DEFAULT AS valor_default
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'two_factor_enabled';

-- Verificar tabla sessions
SELECT 
    'sessions (tabla)' AS elemento,
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ EXISTE'
        ELSE '✗ FALTA - Ejecutar migración'
    END AS estado,
    ENGINE AS motor,
    TABLE_COLLATION AS collation,
    TABLE_COMMENT AS comentario
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'sessions';

-- Verificar índices de sessions
SELECT 
    'sessions - índices' AS elemento,
    GROUP_CONCAT(INDEX_NAME ORDER BY INDEX_NAME SEPARATOR ', ') AS indices,
    CASE 
        WHEN COUNT(*) >= 3 THEN '✓ COMPLETO'
        ELSE '⚠ INCOMPLETO'
    END AS estado
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'sessions';

-- =====================================================
-- PARTE 2: DATOS DE PRUEBA
-- =====================================================

SELECT '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep;
SELECT 'VERIFICACIÓN DE DATOS' AS mensaje;
SELECT '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep;

-- Contar usuarios con 2FA activado
SELECT 
    'Usuarios con 2FA activo' AS metrica,
    COUNT(*) AS cantidad,
    CASE 
        WHEN COUNT(*) > 0 THEN 'Hay usuarios usando 2FA'
        ELSE 'Ningún usuario ha activado 2FA aún (normal en ambiente nuevo)'
    END AS observacion
FROM users 
WHERE two_factor_enabled = 1;

-- Contar sesiones activas
SELECT 
    'Sesiones en base de datos' AS metrica,
    COUNT(*) AS cantidad,
    CASE 
        WHEN COUNT(*) > 0 THEN 'Sistema usando DB sessions correctamente'
        ELSE 'Sin sesiones activas (normal si nadie está conectado)'
    END AS observacion
FROM sessions;

-- Verificar audit logs de eventos 2FA
SELECT 
    'Eventos 2FA en audit_logs' AS metrica,
    COUNT(*) AS cantidad,
    GROUP_CONCAT(DISTINCT action ORDER BY action SEPARATOR ', ') AS acciones_registradas
FROM audit_logs 
WHERE action IN ('2fa_enabled', '2fa_disabled', '2fa_failed');

-- =====================================================
-- PARTE 3: RESUMEN FINAL
-- =====================================================

SELECT '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep;
SELECT 'RESUMEN FINAL' AS mensaje;
SELECT '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep, '=' AS sep;

SELECT 
    CASE
        WHEN 
            (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'two_factor_secret') > 0
            AND (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'two_factor_enabled') > 0
            AND (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sessions') > 0
        THEN '✅ PHASE 3 COMPLETAMENTE IMPLEMENTADO'
        ELSE '❌ FALTAN COMPONENTES - Revisar resultados arriba'
    END AS estado_general;

-- Mostrar próximos pasos
SELECT 
    '1. Si todo marca ✓ EXISTE, no necesitas ejecutar ningún script adicional' AS paso_1,
    '2. Sincroniza el código PHP de la aplicación (git pull / FTP)' AS paso_2,
    '3. Prueba el login y configuración de 2FA en /profile/settings' AS paso_3,
    '4. Revisa los logs en /admin/logs' AS paso_4;

-- =====================================================
-- FIN DEL SCRIPT DE VERIFICACIÓN
-- =====================================================

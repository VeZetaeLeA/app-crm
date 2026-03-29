-- ============================================================================
-- Vezetaelea - MIGRACIÓN: IMÁGENES DE CATEGORÍAS Y PLANES FALTANTES
-- Fecha: 2026-02-07
-- ============================================================================

USE `Vezetaelea`;

-- 1. Agregar columna 'image' a service_categories (si no existe)
ALTER TABLE `service_categories` ADD COLUMN `image` VARCHAR(255) DEFAULT NULL AFTER `icon`;

-- 2. Actualizar imágenes para los 4 pilares principales
-- Nota: Rutas de imágenes sugeridas (deberán cargarse en public/assets/images/)
UPDATE `service_categories` SET `image` = '/Vezetaelea/public/assets/images/pillar_etl.png' WHERE `slug` = 'etl-data-warehousing';
UPDATE `service_categories` SET `image` = '/Vezetaelea/public/assets/images/pillar_bi.png' WHERE `slug` = 'big-data-bi';
UPDATE `service_categories` SET `image` = '/Vezetaelea/public/assets/images/pillar_web.png' WHERE `slug` = 'desarrollo-web-apps';
UPDATE `service_categories` SET `image` = '/Vezetaelea/public/assets/images/pillar_ops.png' WHERE `slug` = 'optimizacion-procesos';

-- 3. Asegurar que TODOS los servicios tengan al menos un plan básico para no romper el flujo dinámico
-- Primero identificamos servicios sin planes y les insertamos uno genérico
INSERT INTO `service_plans` (`service_id`, `name`, `level`, `price`, `currency`, `features`, `is_featured`, `is_active`)
SELECT s.id, 'Plan Inicial', 'basic', 499.00, 'USD', '["Análisis preliminar", "Configuración base", "Soporte técnico", "Garantía de implementación"]', 1, 1
FROM `services` s
LEFT JOIN `service_plans` sp ON s.id = sp.service_id
WHERE sp.id IS NULL;

-- 4. Casos específicos mencionados por el usuario (Big Data & BI - Data Lake Solutions)
-- Aseguramos planes para servicios específicos si aún no tienen
INSERT INTO `service_plans` (`service_id`, `name`, `level`, `price`, `currency`, `features`, `is_featured`, `is_active`)
SELECT id, 'Enterprise Data Lake', 'medium', 2499.00, 'USD', '["Arquitectura Cloud", "Seguridad Avanzada", "Escalabilidad Petabytes", "Soporte 24/7"]', 1, 1
FROM `services` 
WHERE `slug` = 'data-lake-solutions' 
AND id NOT IN (SELECT service_id FROM service_plans);

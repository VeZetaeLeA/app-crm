-- ============================================================================
-- VeZetaeLeA OS — DATOS INICIALES (SEED)
-- Versión: 2.0.0
-- Fecha: 2026-03-18
-- Base de Datos: vezetaelea
-- ============================================================================
-- Ejecutar DESPUÉS de schema_vezetaelea.sql
-- ============================================================================

USE `vezetaelea`;

-- ============================================================================
-- PERMISOS GRANULARES (Phase 4 RBAC)
-- ============================================================================
INSERT IGNORE INTO `permissions` (`name`, `description`) VALUES
('manage_leads',    'Gestión del CRM de Leads'),
('manage_projects', 'Gestión de Proyectos'),
('manage_finance',  'Facturas y Presupuestos'),
('manage_services', 'Catálogo de Servicios'),
('manage_cms',      'Blog y Páginas Públicas'),
('view_reports',    'Ver Analytics del Dashboard'),
('manage_users',    'Administración de Usuarios');

-- ============================================================================
-- USUARIOS DE DEMOSTRACIÓN
-- Contraseña para todos: password  (hash bcrypt estándar de Laravel/PHP)
-- ============================================================================
INSERT INTO `users` (`uuid`, `name`, `email`, `phone`, `company`, `password`, `role`, `is_active`, `email_verified_at`) VALUES
(UUID(), 'Administrador VeZetaeLeA', 'admin@vezetaelea.com',  '+54 11 0000-0001', 'VeZetaeLeA',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',  1, NOW()),
(UUID(), 'Staff Demo',               'staff@vezetaelea.com',  '+54 11 0000-0002', 'VeZetaeLeA',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff',  1, NOW()),
(UUID(), 'Cliente Demo',             'cliente@demo.com',      '+54 11 0000-0003', 'Empresa Demo S.A.', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 1, NOW());

-- ============================================================================
-- CATEGORÍAS DE SERVICIOS
-- (Pilares estratégicos de VeZetaeLeA)
-- ============================================================================
INSERT INTO `service_categories` (`name`, `slug`, `description`, `icon`, `order_position`, `is_active`) VALUES
('ETL & Data Warehousing',       'etl-data-warehousing',   'Integración de datos robusta, pipelines automatizados y almacenamiento escalable de grado corporativo. SQL Server, SSIS, Databricks, PySpark.',    'database',   1, 1),
('Big Data & Business Intelligence','big-data-bi',          'Inteligencia de negocios avanzada, visualización predictiva y análisis de datos en tiempo real. Power BI, Looker Studio, Tableau, Qlik.',         'bar_chart',  2, 1),
('Desarrollo Web & Apps',         'desarrollo-web-apps',   'Desarrollo de ecosistemas digitales modernos y aplicaciones centradas en el valor de los datos. PHP, Python, Node.js, frameworks modernos.',       'code',       3, 1),
('Optimización de Procesos',      'optimizacion-procesos', 'Automatización inteligente y eficiencia operativa mediante modelos algorítmicos avanzados. Análisis funcional, documentación, IA.',                 'settings',   4, 1);

-- ============================================================================
-- SERVICIOS
-- ============================================================================
INSERT INTO `services`
    (`category_id`, `name`, `slug`, `short_description`, `full_description`, `icon`, `is_featured`, `is_active`, `order_position`)
VALUES
-- ETL & Data Warehousing
(1, 'Data Pipeline Pro',    'data-pipeline-pro',   'Pipelines ETL de alto rendimiento.',                    'Diseño e implementación de pipelines ETL robustos utilizando las mejores prácticas de la industria.', 'hub',          1, 1, 1),
(1, 'Warehouse Sync',       'warehouse-sync',       'Sincronización en tiempo real.',                        'Servicio de sincronización bidireccional entre data warehouses y sistemas cloud.',                   'sync_alt',     0, 1, 2),
(1, 'Legacy Migration',     'legacy-migration',     'Migración segura de sistemas legacy.',                  'Migración completa desde Oracle, SQL Server, AS400 hacia plataformas modernas cloud o híbridas.',     'history_edu',  0, 1, 3),
(1, 'Real-time Streaming',  'real-time-streaming',  'Procesamiento de datos en tiempo real.',                'Kafka, Spark Streaming o Azure Event Hubs para datos en tiempo real con baja latencia.',              'stream',       0, 1, 4),
-- Big Data & BI
(2, 'Dashboard Enterprise', 'dashboard-enterprise', 'Dashboards ejecutivos con Power BI y Tableau.',         'Diseño de dashboards interactivos para la toma de decisiones estratégicas.',                          'analytics',    1, 1, 1),
(2, 'Data Lake Solutions',  'data-lake-solutions',  'Arquitectura de Data Lake para almacenamiento masivo.', 'Diseño e implementación de Data Lakes en Azure, AWS o GCP a escala de petabytes.',                     'storage',      0, 1, 2),
(2, 'Predictive Analytics', 'predictive-analytics', 'Modelos predictivos y machine learning.',               'Modelos predictivos con Python y R para anticipar tendencias de negocio.',                            'insights',     0, 1, 3),
-- Web & Apps
(3, 'Landing Pages',            'landing-pages',            'Páginas de alta conversión.',           'Diseño y desarrollo de landing pages optimizadas para SEO y conversión.',                           'web',             0, 1, 1),
(3, 'Sistemas Web Complejos',   'sistemas-web-complejos',   'Desarrollo de sistemas web a medida.',  'Desarrollo full-stack con PHP, Python, Node.js y frameworks modernos.',                               'developer_board', 1, 1, 2),
(3, 'Implementación CRM',       'implementacion-crm',       'Bitrix24, Dynamics, Odoo.',             'Configuración, personalización e integración de sistemas CRM y ERP.',                                'group',           0, 1, 3),
-- Optimización de Procesos
(4, 'Consultoría de Procesos',  'consultoria-procesos',     'Análisis y optimización de procesos.',  'Levantamiento, documentación y optimización de procesos con metodologías ágiles.',                   'trending_up',     1, 1, 1),
(4, 'Automatización RPA',       'automatizacion-rpa',       'Automatización robótica de tareas.',    'Bots RPA para automatizar tareas repetitivas y liberar recursos humanos.',                           'smart_toy',       0, 1, 2),
(4, 'Implementación IA',        'implementacion-ia',        'IA integrada en tus procesos.',         'Soluciones de IA para chatbots, análisis de documentos, optimización de procesos y más.',            'psychology',      0, 1, 3);

-- ============================================================================
-- PLANES DE SERVICIOS
-- ============================================================================
INSERT INTO `service_plans` (`service_id`, `name`, `level`, `price`, `currency`, `features`, `is_featured`, `is_active`) VALUES
-- Data Pipeline Pro (service_id = 1)
(1, 'Básico',   'basic',    499.00, 'USD', '["Hasta 5 fuentes de datos","Sincronización diaria","Soporte por email","Dashboard básico"]',                                                              0, 1),
(1, 'Medio',    'medium',   999.00, 'USD', '["Hasta 15 fuentes","Sincronización por hora","Soporte prioritario","Dashboard avanzado","Alertas automáticas"]',                                          1, 1),
(1, 'Avanzado', 'advanced',1999.00, 'USD', '["Fuentes ilimitadas","Sincronización en tiempo real","Soporte 24/7","Dashboard personalizado","Alertas y notificaciones","SLA garantizado"]',             0, 1),
-- Dashboard Enterprise (service_id = 5)
(5, 'Básico',   'basic',    299.00, 'USD', '["1 dashboard","5 visualizaciones","Actualización diaria","Capacitación básica"]',                                                                         0, 1),
(5, 'Medio',    'medium',   699.00, 'USD', '["3 dashboards","15 visualizaciones","Actualización por hora","Capacitación completa","Drill-down interactivo"]',                                         1, 1),
(5, 'Avanzado', 'advanced',1299.00, 'USD', '["Dashboards ilimitados","Visualizaciones ilimitadas","Tiempo real","Capacitación avanzada","Integración API","Móvil y tablets"]',                        0, 1),
-- Sistemas Web Complejos (service_id = 9)
(9, 'Básico',   'basic',   1500.00, 'USD', '["Hasta 5 módulos","Base de datos MySQL","Responsive design","3 meses de soporte"]',                                                                       0, 1),
(9, 'Medio',    'medium',  3500.00, 'USD', '["Hasta 12 módulos","BD escalable","API REST","6 meses de soporte","Integración de pagos"]',                                                               1, 1),
(9, 'Avanzado', 'advanced',7500.00, 'USD', '["Módulos ilimitados","Microservicios","API GraphQL","12 meses de soporte","CI/CD","Testing automatizado"]',                                               0, 1),
-- Consultoría de Procesos (service_id = 11)
(11, 'Básico',  'basic',    800.00, 'USD', '["Análisis de 1 proceso","Documentación","Recomendaciones básicas"]',                                                                                      0, 1),
(11, 'Medio',   'medium',  2000.00, 'USD', '["Análisis de 5 procesos","Documentación BPMN","Plan de mejora","Seguimiento 1 mes"]',                                                                    1, 1),
(11, 'Avanzado','advanced',5000.00, 'USD', '["Análisis integral","Documentación completa","Implementación de mejoras","Seguimiento 3 meses","KPIs de proceso"]',                                      0, 1);

-- ============================================================================
-- CATEGORÍAS DEL BLOG
-- ============================================================================
INSERT INTO `blog_categories` (`name`, `slug`, `description`, `color`, `is_active`) VALUES
('Engineering',          'engineering',          'Artículos técnicos sobre ingeniería de datos y desarrollo de software.',    '#3B82F6', 1),
('Business Intelligence','business-intelligence','Tendencias y mejores prácticas en BI y visualización de datos.',            '#8B5CF6', 1),
('AI & Machine Learning','ai-machine-learning',  'Inteligencia artificial, machine learning y automatización.',               '#EC4899', 1),
('Business Strategy',    'business-strategy',    'Estrategia empresarial y optimización de procesos.',                       '#F59E0B', 1),
('Tutoriales',           'tutoriales',           'Guías paso a paso y tutoriales prácticos.',                                '#10B981', 1);

-- ============================================================================
-- FIN DE LOS DATOS INICIALES — VeZetaeLeA OS v2.0.0
-- ============================================================================

-- ============================================================================
-- Vezetaelea OS - DATOS INICIALES (SEED)
-- Versión: 1.0.0
-- Fecha: 2026-02-04
-- ============================================================================

USE `Vezetaelea`;

-- ============================================================================
-- USUARIO ADMINISTRADOR POR DEFECTO
-- Email: admin@Vezetaelea.com
-- Password: Admin123! (hash bcrypt)
-- ============================================================================
INSERT INTO `users` (`uuid`, `name`, `email`, `phone`, `company`, `password`, `role`, `is_active`, `email_verified_at`) VALUES
(UUID(), 'Administrador Vezetaelea', 'admin@Vezetaelea.com', '+1 234 567 8900', 'Vezetaelea', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW()),
(UUID(), 'Staff Demo', 'staff@Vezetaelea.com', '+1 234 567 8901', 'Vezetaelea', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 1, NOW()),
(UUID(), 'Cliente Demo', 'cliente@demo.com', '+1 234 567 8902', 'Empresa Demo S.A.', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 1, NOW());

-- ============================================================================
-- CATEGORÍAS DE SERVICIOS
-- ============================================================================
INSERT INTO `service_categories` (`name`, `slug`, `description`, `icon`, `order_position`, `is_active`) VALUES
('ETL & Data Warehousing', 'etl-data-warehousing', 'Integración de datos robusta, pipelines automatizados y almacenamiento escalable de grado corporativo. SQL Server, SSIS, Databricks, PySpark.', 'database', 1, 1),
('Big Data & Business Intelligence', 'big-data-bi', 'Inteligencia de negocios avanzada, visualización predictiva y análisis de datos en tiempo real. Power BI, Looker Studio, Tableau, Qlik.', 'bar_chart', 2, 1),
('Desarrollo Web & Apps', 'desarrollo-web-apps', 'Desarrollo de ecosistemas digitales modernos y aplicaciones centradas en el valor de los datos. PHP, Python, Node.js, frameworks modernos.', 'code', 3, 1),
('Optimización de Procesos', 'optimizacion-procesos', 'Automatización inteligente y eficiencia operativa mediante modelos algorítmicos avanzados. Análisis funcional, documentación, IA.', 'settings', 4, 1);

-- ============================================================================
-- SERVICIOS
-- ============================================================================
INSERT INTO `services` (`category_id`, `name`, `slug`, `short_description`, `full_description`, `icon`, `is_featured`, `is_active`, `order_position`) VALUES
-- ETL & Data Warehousing
(1, 'Data Pipeline Pro', 'data-pipeline-pro', 'Pipelines ETL de alto rendimiento para transformación de datos empresariales.', 'Diseño e implementación de pipelines ETL robustos utilizando las mejores prácticas de la industria. Integramos múltiples fuentes de datos en un almacén unificado.', 'hub', 1, 1, 1),
(1, 'Warehouse Sync', 'warehouse-sync', 'Sincronización en tiempo real entre sistemas de datos heterogéneos.', 'Servicio de sincronización bidireccional entre data warehouses, bases de datos operacionales y sistemas cloud.', 'sync_alt', 0, 1, 2),
(1, 'Legacy Migration', 'legacy-migration', 'Migración segura de sistemas legacy a arquitecturas modernas.', 'Migración completa de datos desde sistemas legacy (Oracle, SQL Server, AS400) hacia plataformas modernas cloud o híbridas.', 'history_edu', 0, 1, 3),
(1, 'Real-time Streaming', 'real-time-streaming', 'Procesamiento de datos en tiempo real con baja latencia.', 'Implementación de arquitecturas de streaming con Apache Kafka, Spark Streaming o Azure Event Hubs para datos en tiempo real.', 'stream', 0, 1, 4),

-- Big Data & BI
(2, 'Dashboard Enterprise', 'dashboard-enterprise', 'Dashboards ejecutivos con Power BI y Tableau.', 'Diseño y desarrollo de dashboards interactivos para la toma de decisiones estratégicas. Visualización de KPIs clave del negocio.', 'analytics', 1, 1, 1),
(2, 'Data Lake Solutions', 'data-lake-solutions', 'Arquitectura de Data Lake para almacenamiento masivo.', 'Diseño e implementación de Data Lakes en Azure, AWS o GCP para almacenamiento y análisis de datos a escala de petabytes.', 'storage', 0, 1, 2),
(2, 'Predictive Analytics', 'predictive-analytics', 'Modelos predictivos y machine learning para negocios.', 'Desarrollo de modelos predictivos utilizando Python, R y plataformas cloud para anticipar tendencias de negocio.', 'insights', 0, 1, 3),

-- Web & Apps
(3, 'Landing Pages', 'landing-pages', 'Páginas de aterrizaje de alta conversión.', 'Diseño y desarrollo de landing pages optimizadas para SEO y conversión con las últimas tecnologías web.', 'web', 0, 1, 1),
(3, 'Sistemas Web Complejos', 'sistemas-web-complejos', 'Desarrollo de sistemas web a medida.', 'Desarrollo full-stack de sistemas web complejos con PHP, Python, Node.js y frameworks modernos.', 'developer_board', 1, 1, 2),
(3, 'Implementación CRM', 'implementacion-crm', 'Implementación de Bitrix24, Dynamics, Odoo.', 'Configuración, personalización e integración de sistemas CRM y ERP para optimizar procesos de negocio.', 'group', 0, 1, 3),

-- Optimización de Procesos
(4, 'Consultoría de Procesos', 'consultoria-procesos', 'Análisis y optimización de procesos empresariales.', 'Levantamiento, documentación y optimización de procesos de negocio con metodologías ágiles.', 'trending_up', 1, 1, 1),
(4, 'Automatización RPA', 'automatizacion-rpa', 'Automatización robótica de procesos repetitivos.', 'Implementación de bots RPA para automatizar tareas repetitivas y liberar recursos humanos.', 'smart_toy', 0, 1, 2),
(4, 'Implementación IA', 'implementacion-ia', 'Integración de inteligencia artificial en procesos.', 'Implementación de soluciones de IA para optimización de procesos, chatbots, análisis de documentos y más.', 'psychology', 0, 1, 3);

-- ============================================================================
-- PLANES DE SERVICIOS
-- ============================================================================
INSERT INTO `service_plans` (`service_id`, `name`, `level`, `price`, `currency`, `features`, `is_featured`, `is_active`) VALUES
-- Data Pipeline Pro
(1, 'Básico', 'basic', 499.00, 'USD', '["Hasta 5 fuentes de datos", "Sincronización diaria", "Soporte por email", "Dashboard básico"]', 0, 1),
(1, 'Medio', 'medium', 999.00, 'USD', '["Hasta 15 fuentes de datos", "Sincronización por hora", "Soporte prioritario", "Dashboard avanzado", "Alertas automáticas"]', 1, 1),
(1, 'Avanzado', 'advanced', 1999.00, 'USD', '["Fuentes ilimitadas", "Sincronización en tiempo real", "Soporte 24/7", "Dashboard personalizado", "Alertas y notificaciones", "SLA garantizado"]', 0, 1),

-- Dashboard Enterprise
(5, 'Básico', 'basic', 299.00, 'USD', '["1 dashboard", "5 visualizaciones", "Actualización diaria", "Capacitación básica"]', 0, 1),
(5, 'Medio', 'medium', 699.00, 'USD', '["3 dashboards", "15 visualizaciones", "Actualización por hora", "Capacitación completa", "Drill-down interactivo"]', 1, 1),
(5, 'Avanzado', 'advanced', 1299.00, 'USD', '["Dashboards ilimitados", "Visualizaciones ilimitadas", "Tiempo real", "Capacitación avanzada", "Integración API", "Móvil y tablets"]', 0, 1),

-- Sistemas Web Complejos
(9, 'Básico', 'basic', 1500.00, 'USD', '["Hasta 5 módulos", "Base de datos MySQL", "Responsive design", "3 meses de soporte"]', 0, 1),
(9, 'Medio', 'medium', 3500.00, 'USD', '["Hasta 12 módulos", "Base de datos escalable", "API REST", "6 meses de soporte", "Integración de pagos"]', 1, 1),
(9, 'Avanzado', 'advanced', 7500.00, 'USD', '["Módulos ilimitados", "Arquitectura microservicios", "API GraphQL", "12 meses de soporte", "CI/CD", "Testing automatizado"]', 0, 1),

-- Consultoría de Procesos
(11, 'Básico', 'basic', 800.00, 'USD', '["Análisis de 1 proceso", "Documentación", "Recomendaciones básicas"]', 0, 1),
(11, 'Medio', 'medium', 2000.00, 'USD', '["Análisis de 5 procesos", "Documentación BPMN", "Plan de mejora", "Seguimiento 1 mes"]', 1, 1),
(11, 'Avanzado', 'advanced', 5000.00, 'USD', '["Análisis integral", "Documentación completa", "Implementación de mejoras", "Seguimiento 3 meses", "KPIs de proceso"]', 0, 1);

-- ============================================================================
-- CATEGORÍAS DEL BLOG
-- ============================================================================
INSERT INTO `blog_categories` (`name`, `slug`, `description`, `color`, `is_active`) VALUES
('Engineering', 'engineering', 'Artículos técnicos sobre ingeniería de datos y desarrollo de software.', '#3B82F6', 1),
('Business Intelligence', 'business-intelligence', 'Tendencias y mejores prácticas en BI y visualización de datos.', '#8B5CF6', 1),
('AI & Machine Learning', 'ai-machine-learning', 'Inteligencia artificial, machine learning y automatización.', '#EC4899', 1),
('Business Strategy', 'business-strategy', 'Estrategia empresarial y optimización de procesos.', '#F59E0B', 1),
('Tutoriales', 'tutoriales', 'Guías paso a paso y tutoriales prácticos.', '#10B981', 1);

-- ============================================================================
-- FIN DE LOS DATOS INICIALES
-- ============================================================================

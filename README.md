# 🌐 VeZetaeLeA OS

<div align="center">

![Version](https://img.shields.io/badge/version-18.2.2-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)
![License](https://img.shields.io/badge/license-Proprietary-red.svg)
![Status](https://img.shields.io/badge/status-SaaS--Ready%20Certified-brightgreen.svg)
![Security](https://img.shields.io/badge/security-Enterprise--Hardened-success.svg)

**Plataforma Enterprise Certificada para Gestión de Servicios de Ingeniería de Datos y Transformación Digital**

[Características](#-características-principales) • [Instalación](#-instalación) • [Deployment](#-deployment) • [Documentación](#-documentación)

</div>

---

## 📖 Descripción

**VeZetaeLeA OS** es una plataforma web completa diseñada para gestionar el ciclo de vida completo de servicios de ingeniería de datos, desde la captación de clientes hasta la entrega de proyectos. Integra un CMS dinámico, sistema de tickets, gestión financiera y workspaces colaborativos.


### 🎯 Casos de Uso

- **Empresas de Consultoría de Datos**: Gestión de proyectos de BI, ETL, y Data Warehousing
- **Agencias de Desarrollo**: Seguimiento de proyectos web y aplicaciones
- **Servicios Profesionales**: Cualquier negocio B2B que requiera gestión de tickets y facturación

---

## ✨ Características Principales

### 🤖 Arquitectura & Core
- ✅ **100% Zero-Hardcode (White-Label Ready)**: Configuración técnica y de marca totalmente dinámica desde el `.env` y el gestor de configuración avanzado.
- ✅ **Sistema de Internacionalización (i18n)**: Soporte bilingüe (ES/EN) nativo mediante carga dinámica de diccionarios JSON y helper `__()`.
- ✅ **Core Dinámico**: URLs escalables, marcas mutables, templates genéricos, e Inyección de Dependencias.

### 🏠 Ecosistema Público

- ✅ **Landing Page Premium** con video background y efectos parallax.
- ✅ **Flujo Dinámico de Servicios** (Pilar → Servicio → Plan → Ticket).
- ✅ **Blog Corporativo High-End**:
  - Hero Parallax con separación de imagen y contenido para legibilidad superior.
  - Filtros unificados con la estética de "Pilares" (colores corporativos Steel/Tech Blue).
  - Sistema de gestión de imágenes optimizado con nomenclatura `ID_SLUG`.
  - Paginación automática y sistema de comentarios inteligente.
- ✅ **CMS Visual** para gestión de servicios, planes e imágenes.
- ✅ **Formularios Inteligentes** con pre-poblado automático.
- ✅ **Optimización SEO de Vanguardia**: Meta tags dinámicos, soporte Open Graph para redes sociales y generación automática de `sitemap.xml`.

### 👥 Sistema Multi-Rol

#### 👑 Panel de Administrador
- Dashboard con analítica en tiempo real (Chart.js)
- Gestión completa de usuarios (Admin/Staff/Clientes)
- Control total del CMS (Servicios, Planes, Blog)
- Generación de presupuestos y facturas en PDF
- Monitoreo de tickets y asignación de staff
- ✅ **Configuración Global desde UI**: Gestión de branding, seguridad y límites del sistema.
- ✅ **Importación Masiva**: Carga de clientes vía CSV con reporte de integridad.

#### 👨‍💼 Panel de Staff
- Vista de tickets asignados con priorización
- Chat bidireccional con clientes
- Carga de entregables al workspace
- Actualización de estados de tickets

#### 👤 Panel de Cliente
- Vista de servicios activos
- Seguimiento de tickets en tiempo real
- Chat con el equipo de soporte
- Descarga de entregables desde workspace
- Historial de facturas y pagos

### 💼 Gestión Operativa & Inteligencia (Evolución Sprint 2)

- ✅ **Kanban de Tickets**: Tablero interactivo (Drag & Drop) para gestión ágil del flujo comercial.
- ✅ **Project Timeline**: Línea de tiempo visual con KPIs de avance y hitos de entregables.
- ✅ **Gestión de SLA**: Monitoreo proactivo de tiempos de respuesta y resolución con alertas visuales.
- ✅ **Aprobación de Entregables**: Ciclo de feedback interactivo cliente-staff con notificaciones automatizadas.
- ✅ **Business Intelligence**: Dashboards ejecutivos con KPIs de conversión y financieros.
- ✅ **Automation Engine**: Motor de reglas personalizables para disparar acciones automáticas.
- ✅ **Workspace Inteligente**: Recomendaciones proactivas y alertas basadas en IA de negocio.
- ✅ **Observabilidad**: Monitoreo de performance y métricas vitales del sistema.
- ✅ **Multi-Tenant Foundation**: Infraestructura lista para escalar como plataforma SaaS.
- ✅ **Sistema de Tickets** con estados dinámicos y eventos de automatización.
- ✅ **Chat Integrado** para comunicación cliente-staff.
- ✅ **Generación de Presupuestos** con items personalizables.
- ✅ **Facturación Automática** con tracking de pagos y webhooks de MercadoPago.
- ✅ **Workspace de Proyectos** para intercambio de archivos con control de versiones.
- ✅ **Identidad Corporativa Estandarizada** (.env).

### 🔒 Seguridad Enterprise
- ✅ **Doble Factor de Autenticación (2FA)**: Soporte TOTP (Google Authenticator) obligatorio para administración.
- ✅ **Criptografía Argon2id**: Hashing de contraseñas de última generación.
- ✅ **Auditoría Forense Inmutable**: Logs con firma SHA256 para prevenir manipulaciones.
- ✅ **IP & Account Rate Limiting**: Protección estricta contra ataques de fuerza bruta.
- ✅ **Protección CSRF Global**: Filtro automático en el router para todas las peticiones POST.
- ✅ **Hardening de Sesiones**: Cookies seguras (HttpOnly, Secure, SameSite) y regeneración de ID.
- ✅ **Validación MIME Estricta**: Verificación de contenido real en subida de archivos.
- ✅ **Blindaje de Directorios**: Acceso prohibido vía `.htaccess` a `/App`, `/Core` y `/storage`.
- ✅ **Zero-Hardcode Infrastructure**: Configuración 100% dinâmica vía `.env` sin fallbacks locales.
- ✅ **Logging Seguro**: Errores registrados silenciosamente fuera de la raíz pública.
- ✅ **Prepared Statements**: Protección total contra SQL Injection.

---

## 🛠️ Stack Tecnológico

### Backend
- **PHP 8.0+** - Lenguaje principal
- **MySQL 8.0+** - Base de datos relacional
- **Composer** - Gestión de dependencias y Autoloading PSR-4
- **firebase/php-jwt** - Protocolo de seguridad para API
- **PHPUnit** - Suite de pruebas automatizadas
- **PDO** - Capa de abstracción de base de datos
- **Custom MVC Framework** - Arquitectura propia balanceada con eventos

### Frontend
- **HTML5 / CSS3** - Estructura y estilos
- **Bootstrap 5** - Framework CSS
- **JavaScript Vanilla** - Interactividad
- **Chart.js** - Visualización de datos
- **Material Symbols** - Iconografía

### Infraestructura
- **Apache / Nginx** - Servidor web
- **Hostinger** - Hosting recomendado
- **Zoho Mail** - Servicio de email SMTP
- **Let's Encrypt** - SSL/TLS

---

## 📋 Requisitos

### Servidor

- PHP >= 8.0
- MySQL >= 8.0
- Apache/Nginx con mod_rewrite
- SSL/HTTPS habilitado
- Extensiones PHP:
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `json`
  - `fileinfo`
  - `curl`

### Desarrollo Local

- XAMPP / WAMP / MAMP
- Composer (opcional)
- Git
- Editor de código (VS Code recomendado)

---

## 🚀 Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/VeZetaeLeA.git
cd VeZetaeLeA
```

### 2. Configurar Base de Datos

```bash
# Crear base de datos
mysql -u root -p
CREATE DATABASE VeZetaeLeA CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Importar esquema
mysql -u root -p VeZetaeLeA < database/schema.sql
# ... ejecutar migraciones secuenciales en database/migrations/ ...
```

### 3. Instalar Dependencias
```bash
composer install
```

### 3. Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar .env con tus credenciales
nano .env
```

**Variable Clave:**
- `ENVIRONMENT`: Define el comportamiento del sistema (`local`, `demo`, `production`).

**Configuración mínima (.env):**
```env
ENVIRONMENT=local
APP_NAME="VeZetaeLeA OS"
DB_HOST=localhost
DB_DATABASE=VeZetaeLeA
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Arquitectura de Configuración
El sistema utiliza una arquitectura jerárquica en `/config` gestionada por la clase `Core\Config`:
- `app.php`: Configuración común.
- `{ENVIRONMENT}.php`: Overrides por entorno (URLs, Debug, Email).

**Acceso unificado:**
```php
use Core\Config;
$val = Config::get('clave.subclave');
```

### 4. Configurar Servidor Web

**Apache (.htaccess ya incluido):**

```apache
# Asegúrate de que mod_rewrite esté habilitado
sudo a2enmod rewrite
sudo service apache2 restart
```

**Nginx:**

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/VeZetaeLeA/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### 5. Configurar Permisos

```bash
chmod -R 755 storage
chmod -R 777 storage/logs
chmod -R 777 public/storage/uploads
chmod 600 .env
```

### 6. Acceder a la Aplicación

```
http://localhost/VeZetaeLeA
```

**Credenciales por defecto:**

```
Admin:
Email: admin@vezetaelea.com
Password: admin123

Staff:
Email: staff@vezetaelea.com
Password: staff123

Cliente:
Email: cliente@vezetaelea.com
Password: cliente123
```

⚠️ **IMPORTANTE:** Cambiar estas contraseñas inmediatamente en producción.

---

## 🌐 Deployment

Para desplegar en producción (Hostinger + Zoho Mail), consulta la guía completa:

📘 **[docs/DEPLOYMENT_GUIDE.md](docs/DEPLOYMENT_GUIDE.md)**

### Checklist Rápido

- [ ] Configurar `.env` para producción (`APP_DEBUG=false`)
- [ ] Configurar base de datos en Hostinger
- [ ] Subir archivos por FTP/SFTP
- [ ] Configurar Zoho Mail SMTP
- [ ] Habilitar SSL/HTTPS
- [ ] Verificar permisos de archivos
- [ ] Probar todas las funcionalidades

---

## 📚 Documentación

Encuentra todos los detalles técnicos y de configuración en el nuevo ecosistema de documentación `/docs`.

### Índice Principal
📘 **[docs/README.md](docs/README.md)**

### Documentos Core
| Documento | Descripción |
|-----------|-------------|
| [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) | Resumen completo del proyecto (estado del arte). |
| [docs/DEPLOYMENT_GUIDE.md](docs/DEPLOYMENT_GUIDE.md) | Guía paso a paso para deployment en Hostinger. |
| [docs/CODE_ANALYSIS.md](docs/CODE_ANALYSIS.md) | Análisis profundo del código y la arquitectura. |
| [docs/SECURITY.md](docs/SECURITY.md) | Manual de mejores prácticas de seguridad. |
| [docs/ENV_CONFIGURATION_GUIDE.md](docs/ENV_CONFIGURATION_GUIDE.md) | Guía de variables de entorno. |
| [docs/MERCADOPAGO_INTEGRATION.md](docs/MERCADOPAGO_INTEGRATION.md) | Manual técnico para incorporar llaves y webhooks de MP. |

---

## 🏗️ Arquitectura

### Estructura de Directorios

```
VeZetaeLeA/
├── App/
│   ├── Controllers/        # Controladores MVC (Delgados)
│   ├── Domain/             # Capa de Dominio (Pure Logic)
│   │   ├── ActiveService/
│   │   ├── Ticket/
│   │   └── Invoice/
│   ├── Services/           # Servicios de Negocio y Orquestación
│   ├── Validators/         # Validadores especializados
│   ├── Policies/           # Políticas de Autorización centralizada
│   ├── UI/                 # Design System (PHP Components)
│   ├── Models/            # Modelos de datos (Persistencia)
│   └── Views/             # Vistas inteligentes organizadas por rol
│       ├── admin/
│       ├── staff/
│       ├── client/
│       ├── public/
│       └── layouts/
├── config/                # Configuración Jerárquica
│   ├── app.php            # Común
│   ├── local.php          # Desarrollo
│   ├── demo.php           # Pre-producción
│   ├── production.php     # Producción
├── Core/                  # Framework core (Case-Sensitive)
│   ├── App.php           # Router principal
│   ├── Auth.php          # Autenticación
│   ├── Config.php        # Gestor de configuración
│   ├── Controller.php    # Controlador base
│   ├── Database.php      # Singleton de BD
│   ├── Mail.php          # Sistema de emails
│   ├── Model.php         # Modelo base
│   ├── Session.php       # Gestión de sesiones
│   ├── Validator.php     # Validación y sanitización
│   └── View.php          # Renderizado de vistas
├── database/             # Esquemas y migraciones
├── public/               # Raíz pública
│   ├── assets/
│   ├── storage/          # Uploads públicos (symlink)
│   ├── .htaccess
│   └── index.php         # Entry point & Global Helpers
├── storage/              # Almacenamiento privado
```
```

### Flujo de Peticiones

```
1. Usuario → public/index.php
2. Autoloader carga clases necesarias
3. Config::load() carga variables de entorno
4. Session::start() inicia sesión
5. App::__construct() parsea URL
6. Router determina Controller y Method
7. Controller ejecuta lógica de negocio
8. View renderiza respuesta HTML
9. Respuesta → Usuario
```

---

## 🔧 Desarrollo

### Añadir un Nuevo Controlador

```php
<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;

class MiControlador extends Controller
{
    public function __construct()
    {
        // Verificar autenticación si es necesario
        if (!Auth::check()) {
            $this->redirect('/auth/login');
        }
    }

    public function index()
    {
        $data = ['title' => 'Mi Página'];
        $this->viewLayout('mi-vista', 'admin', $data);
    }
}
```

### Añadir una Nueva Ruta

Las rutas se mapean automáticamente:

```
URL: /mi-controlador/metodo/param1
Mapea a: MiControladorController->metodo($param1)
```

### Validar Inputs

```php
use Core\Validator;

$validator = new Validator();
$validator->validate($_POST, [
    'email' => 'required|email',
    'name' => 'required|min:3|max:100',
    'age' => 'numeric'
]);

if ($validator->fails()) {
    Session::flash('errors', $validator->errors());
    $this->redirect('/form');
}

// Sanitizar
$email = Validator::sanitizeEmail($_POST['email']);
$name = Validator::sanitizeString($_POST['name']);
```

### Proteger Formularios con CSRF

```php
// En la vista
<form method="POST">
    <?= csrf_field() ?>
    <!-- campos del formulario -->
</form>

// La validación es AUTOMÁTICA en Core\App.php
```

---

## 🧪 Testing

El proyecto incorpora un entorno base para testing unitario automatizado utilizando PHPUnit.

```bash
# Ejecutar tests
php vendor/bin/phpunit tests/Unit/InvoiceServiceTest.php

# Instalar PHPUnit (si no está instalado)
composer require --dev phpunit/phpunit

# Estructura de tests
tests/
├── Unit/
│   ├── AuthTest.php
│   └── ValidatorTest.php
└── Feature/
    ├── LoginTest.php
    └── TicketTest.php
```

---

## 🤝 Contribución

Este es un proyecto propietario. Para contribuir:

1. Crear una rama feature: `git checkout -b feature/nueva-funcionalidad`
2. Commit de cambios: `git commit -m 'Añadir nueva funcionalidad'`
3. Push a la rama: `git push origin feature/nueva-funcionalidad`
4. Crear Pull Request

### Estándares de Código

- Seguir PSR-1 y PSR-2
- Documentar funciones con docblocks
- Nombres de variables en español (consistencia con el proyecto)
- Nombres de métodos y clases en inglés

---

## 🐛 Solución de Problemas

### Error 500 - Internal Server Error

```bash
# Verificar logs
tail -f storage/logs/error.log

# Verificar permisos
chmod -R 755 storage
```

### Base de Datos No Conecta

```bash
# Verificar credenciales en .env
# Verificar que MySQL esté corriendo
sudo service mysql status
```

### Emails No Se Envían

```bash
# Verificar configuración SMTP en .env
# Ver logs de email
tail -f storage/logs/mail.log
```

Para más soluciones, consulta la Sección 10 de [docs/DEPLOYMENT_GUIDE.md](docs/DEPLOYMENT_GUIDE.md)

---

## 📊 Roadmap

- [x] Evolución 2.0 a 2.6.0: Premium Admin UI, Executive Design & FinOps integration.
- [x] Estandarización de Identidad Corporativa (Branding Dinámico vía `.env`).
- [x] Implementación de Referencia de Servicio (Pilar-Servicio) en flujo comercial.
- [x] Auditoría visual de grillas y armonía UX en vistas de Budget/Invoice.
- [x] Hardening de Seguridad: CSP, HSTS, Rate Limiting y Zero-Hardcode Certified.

### 📊 Versión 3.0 (Desplegada)
- [x] Evolución 10.0: Inteligencia, Automatización y Multi-Tenant (Desplegado).
- [x] Dashboard de Business Intelligence con **Funnel Chart** interactivo.
- [x] Sistema de **Insight Alerts** proactivos basados en IA de negocio.
- [x] Motor de Reglas (`RuleEngine`) para automatización de procesos.
- [x] Ecosistema de Observabilidad y Métricas de Negocio.
- [x] Infraestructura Multi-Tenant (Middleware & DB Isolation).
- [x] **Sistema i18n Global**: Soporte nativo multi-idioma.

### 🔮 Próximos Pasos (Evolución 11.0 y Misión Enterprise)
- [x] **Módulo GAI (Generative AI Integration)**: Asistencia LLM vía **Groq (Llama-3.1-8b-instant)** para auto-resúmenes, Copilot Chat UX (TextArea dinámico) y extracción de action items mediante Bypass SSL local.
- [x] **Exportación Core PDF Resiliente**: Generador de facturas y Códigos QR refactorizados (Endroid v6 + SvgWriter), liberando al ecosistema de la extrema dependencia de la extensión `GD` PHP. Operabilidad total garantizada.
- [x] **Seguridad Defensiva**: Blindaje anti-corrupción del `RateLimitMiddleware` y re-ruteo inmersivo de InsightEngine a historiales recientes.
- [ ] **VeZetaeLeA Multi-Tenant & SaaS Core**: Habilitación nativa de inquilinos.
- [ ] **Pipelines CI/CD & Test-Driven**: Extender cobertura PHPUnit al 80% y generar flujos de despliegue inmutables.
- [ ] **CQRS & Event Sourcing (FinOps)**: Auditoría infalible de pagos transaccionales mediante historial de eventos puros.
- [ ] **Dockerización Genérica**: Contenerización de la plataforma para escalabilidad horizontal en clústeres.
- [x] **Real-Time Absoluto**: Integración de WebSockets nativa (Reactor v1).
- [ ] Aplicación Móvil Companion conectada vía API v1.
- [ ] Expansión de motor de reglas para integraciones externas (Webhooks out).
- [ ] Dashboard UI Builder para roles Staff y Cliente.

---

## 📄 Licencia

Este proyecto es **propietario** y confidencial. Todos los derechos reservados.

© 2026 VeZetaeLeA. Prohibida su distribución sin autorización.

---

## 👥 Equipo

**Desarrollado por:** VeZetaeLeA Team  
**Contacto:** contacto@vezetaelea.com  
**Website:** https://www.vezetaelea.com

---

## 🙏 Agradecimientos

- Bootstrap Team por el framework CSS
- Chart.js por las visualizaciones
- Material Design por los iconos
- Comunidad PHP por las mejores prácticas

---

<div align="center">

**[⬆ Volver arriba](#-vezetaelea-os)**

Hecho con ❤️ por el equipo de VeZetaeLeA

</div>

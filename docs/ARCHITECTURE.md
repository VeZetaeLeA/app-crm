# VeZetaeLeA OS — Arquitectura Técnica
## Versión 2.4.0 · Actualizado: 2026-03-20

---

## 1. Visión General

**VeZetaeLeA OS** es una plataforma CRM + Portal de Clientes + Sistema de Facturación sobre un framework PHP MVC personalizado.

> **v2.0.0 — Rebranding:** Toda referencia a "DataWyrd" fue reemplazada por "VeZetaeLeA". Los prefijos de IDs son ahora `VZL-` (presupuestos: `VZL-B{AÑO}-{HEX4}`, facturas: `VZL-INV-{YYYYMMDD}-{HEX4}`).

---

## 2. Stack Tecnológico

| Capa | Tecnología |
|------|-----------|
| **Servidor** | Apache 2.4 + mod_rewrite (XAMPP dev) / Nginx producción |
| **Backend** | PHP 8.1+ (MVC custom) |
| **Base de Datos** | MySQL 8.0+ / MariaDB 10.6+ — BD: `vezetaelea` |
| **Frontend** | HTML5 + Vanilla CSS + Bootstrap 5.3 + Vanilla JS |
| **PDF** | Dompdf 3.x |
| **QR Codes** | Endroid QR Code |
| **Email** | PHPMailer 6.x |
| **AI** | OpenAI API (gpt-4o-mini) |
| **Pagos** | MercadoPago SDK |
| **Fuentes** | Outfit + Space Grotesk + Material Symbols (Google) |

---

## 3. Estructura de Directorios (Resumida)

```
app-crm/
├── App/
│   ├── Controllers/          # BudgetController, InvoiceController, TicketController...
│   ├── Domain/               # Value Objects (InvoiceStatus)
│   ├── Models/               # User, Service, Notification
│   ├── Repositories/         # InvoiceRepository (Patrón Repository E11-001)
│   ├── Policies/             # InvoicePolicy
│   ├── Services/             # AIService, InvoiceService, FinOpsService, DashboardService, CRM/...
│   ├── Jobs/                 # SendEmailJob (cola async)
│   └── Views/
│       ├── layouts/          # public.php, staff.php, client.php, admin.php
│       ├── public/           # Homepage, blog, errores
│       ├── admin/staff/client/ # Paneles de gestión
│       └── pdf/              # budget.php, invoice.php (Dompdf)
├── Core/                     # Framework: Router, Auth, DB, Mail, Security, JWT...
├── config/app.php            # Configuración centralizada desde .env
├── database/
│   ├── schema_vezetaelea.sql # ESQUEMA CANÓNICO v2.0 (usar este)
│   ├── seed_vezetaelea.sql   # DATOS INICIALES v2.0 (usar este)
│   └── migrations/           # Histórico de migraciones
├── docs/                     # ARCHITECTURE.md, DEPLOYMENT_GUIDE.md, SECURITY.md...
├── public/                   # index.php, .htaccess, assets/
│   └── assets/images/
│       ├── logo.png                   # LOGO ÚNICO (todos los layouts)
│       ├── VeZetaeLeA.ico             # Favicon
│       ├── VeZetaeLeA_home_video.mp4  # Video hero section
│       ├── hero_background.png        # Parallax FAQ/CTA
│       ├── vzl_os_crm.png             # Screenshot CRM
│       ├── vzl_os_ai.png              # Screenshot AI
│       ├── vzl_os_finops.png          # Screenshot FinOps
│       ├── vzl_os_realtime.png        # Screenshot RealTime
│       └── vezetaelea_working.png     # Imagen equipo holográfica
├── worker.php                # Worker cola async
└── .env                      # Variables de entorno (no subir a Git)
```

---

## 4. Base de Datos: `vezetaelea`

### 4.1 Tablas del Sistema

| Tabla | Propósito | Versión añadida |
|-------|-----------|-----------------|
| `users` | Usuarios (admin/staff/client) + 2FA + lead_score | v1.0 + Phase3 + Phase4 |
| `sessions` | Sesiones en BD con metadatos de seguridad | Phase 3 |
| `service_categories` | Pilares estratégicos (ETL, BI, Web, Procesos) | v1.0 |
| `services` | Servicios individuales | v1.0 |
| `service_plans` | Planes básico/medio/avanzado por servicio | v1.0 |
| `tickets` | Solicitudes de servicio (flujo comercial) | v1.0 |
| `ticket_tasks` | Action items IA Copilot GAI-02 | Sprint 2 |
| `chat_messages` | Mensajes de chat por ticket | v1.0 |
| `ticket_attachments` | Adjuntos por ticket | v1.0 |
| `budgets` | Presupuestos versionados (parent_budget_id) | v1.0 + Sprint 1 |
| `budget_items` | Ítems del presupuesto | v1.0 |
| `invoices` | Facturas con MercadoPago | v1.0 + MP |
| `invoice_events` | Event Store inmutable (Event Sourcing) | Sprint 4 |
| `payment_receipts` | Comprobantes de pago | v1.0 |
| `active_services` | Servicios activados post-pago | v1.0 |
| `blog_categories` | Categorías del blog | v1.0 |
| `blog_posts` | Posts administrados desde panel | v1.0 |
| `notifications` | Centro de notificaciones in-app | v1.0 |
| `email_logs` | Historial de correos enviados | v1.0 |
| `audit_logs` | Auditoría inmutable + firma criptográfica | v1.1 + Phase4 |
| `jobs` | Cola de trabajos async (emails, webhooks) | v1.3 |
| `instagram_calendar` | Calendarios de contenido Instagram | Sprint 2 |
| `instagram_posts` | Posts del calendario | Sprint 2 |
| `permissions` | Permisos granulares (RBAC) | Phase 4 |
| `roles_custom` | Roles personalizados | Phase 4 |
| `role_permissions` | Tabla pivote roles-permisos | Phase 4 |

### 4.2 Formato de IDs Generados

| Entidad | Formato | Ejemplo |
|---------|---------|---------|
| Ticket | `TKT-{HEX6}` | `TKT-A1B2C3` |
| Presupuesto | `VZL-B{AÑO}-{HEX4}` | `VZL-B2026-A1B2` |
| Factura | `VZL-INV-{YYYYMMDD}-{HEX4}` | `VZL-INV-20260318-A1B2` |

> **Migracion:** Registros existentes con prefijo `DW-` siguen siendo válidos. Solo los nuevos usan `VZL-`.

---

## 5. Flujo Comercial Completo

```
Homepage → Formulario → TicketController::submit()
    ↓ TKT-{HEX6} creado, email bienvenida enviado, GAI extrae tasks
Staff analiza ticket (status: open → in_analysis)
    ↓
BudgetController::store()
    ↓ VZL-B{AÑO}-{HEX4} generado, email al cliente
Cliente aprueba/rechaza
    Si rechaza → BudgetController::edit() → nueva versión (parent_budget_id)
    ↓
InvoiceController::createFromBudget()
    ↓ VZL-INV-{YYYYMMDD}-{HEX4} generado, invoice_events: CREATE
    ↓ ticket: invoiced
Cliente paga
    MercadoPago Webhook → invoice_events: APPLY_PAYMENT
    O comprobante manual → payment_receipts
    ↓ invoice: paid | ticket: active
Servicio activado → active_services
```

---

## 6. Autenticación y Seguridad

| Mecanismo | Implementación |
|-----------|---------------|
| Contraseñas | Argon2id / Bcrypt via PHP |
| Sesiones | Tabla `sessions` en BD (Phase 3) |
| 2FA | TOTP Google Authenticator (TwoFactor.php) |
| CSRF | Token en cada formulario (`csrf_field()`) |
| Rate Limiting | RateLimiter.php (5 tickets/hora, 5 logins/min) |
| Audit Trail | `audit_logs` + firma criptográfica (`signature_hash`) |
| JWT | Para API REST (Core/JWT.php) |
| RBAC | Roles ENUM (`admin/staff/client`) + granular (Phase 4) |

---

## 7. Correos Electrónicos (Core/Mail.php)

Gradiente de marca en emails: `#D4AF37 (Gold) → #30C5FF (Cyan)`

| Método | Cuándo se envía |
|--------|----------------|
| `sendWelcome()` | Nuevo cliente auto-registrado |
| `sendRequestConfirmation()` | Ticket creado exitosamente |
| `sendTicketUpdate()` | Cambio de estado en ticket |
| `sendBudgetAvailable()` | Presupuesto enviado al cliente |
| `sendUrgentSupport()` | Cliente solicita soporte urgente |

> Dev: `MAIL_ENABLED=false` silencia todos los envíos.

---

## 8. PDFs Branded

### Presupuesto (`pdf/budget.php`)
- Acento: **Cian** (`#00f2ff`)
- Logo: `logo.png` embebido en base64
- Empresa: `COMPANY_NAME` y `COMPANY_SLOGAN` desde `.env`
- Número: formato `VZL-B{AÑO}-{HEX4}`

### Factura (`pdf/invoice.php`)
- Acento: **Magenta** (`#ec4899`)
- QR Code con hash de validación
- Estado de pago (badge CSS)
- Número: formato `VZL-INV-{YYYYMMDD}-{HEX4}`

---

## 9. IA Generativa (Módulo GAI)

| Feature | Código | Descripción |
|---------|--------|-------------|
| GAI-01 | `AIService::summarizeTicket()` | Auto-resumen de tickets con +15 mensajes |
| GAI-02 | `AIService::extractActionItems()` | Extrae tareas al crear ticket → `ticket_tasks` |
| GAI-03 | Chat Copilot | Redacción formal desde bullets del staff |
| Instagram | `InstagramService` | Calendario semanal de contenido |

> Activa con `OPENAI_API_KEY` en `.env`. Modelo: `OPENAI_MODEL=gpt-4o-mini`.

---

## 10. Variables de Entorno Esenciales

```ini
APP_NAME="VeZetaeLeA OS"
APP_URL=http://localhost/app-crm
COMPANY_NAME="VeZetaeLeA"
COMPANY_SLOGAN="Ingeniería de Datos de Vanguardia"

DB_DATABASE=vezetaelea
DB_USERNAME=root
DB_PASSWORD=

MAIL_ENABLED=false
MAIL_FROM_NAME="Contacto VeZetaeLeA"
MAIL_FROM_ADDRESS=contacto@vezetaelea.com

TAX_RATE=21.00
OPENAI_API_KEY=""
OPENAI_MODEL=gpt-4o-mini

MP_ACCESS_TOKEN=
MP_PUBLIC_KEY=
```

---

## 11. Paleta de Colores — Sistema Unificado (v2.4)

### Core Tokens (`variables.css`) — Fuente de Verdad
| Token CSS | Valor HEX | Uso |
|-----------|-----------|-----|
| `--vzl-cyan` | `#399297` | Teal Profundo. CTAs primarios, acentos tech, subtitúlos de sección. |
| `--vzl-magenta` | `#b10da9` | Púrpura Acento. CTAs secundarios, destacados estratégicos. |
| `--vzl-gold` | `#D4AF37` | Oro Premium. Solo para acentos de alta jerarquía. |
| `--vzl-deep-black` | `#020617` | Fondo base del sistema. Inmersivo y de alto contraste. |
| `--vzl-midnight` | `#0f172a` | Superficies de tarjetas y contenedores. |
| `--vzl-white` | `#f8fafc` | Texto primario. Máxima legibilidad. |

### Gradiente Physichromie (Animado)
```
#399297 → #7c3aed → #b10da9 → #f59e0b → #00f2ff
```
Aplicado a `.text-gradient`, `.vzl-text-gradient`, `.vzl-text-gradient-vibrant`.
Animación `vzl-physi-flow` de 8s en loop infinito.

### Estructura de Sección Homepage (`home.php`)
| Sección | ID | Función |
|---------|----|----------|
| Hero | `header.hero` | Propuesta de valor minimalista (H1 + CTAs) |
| Social Proof | `.vzl-social-proof` | Franja de métricas clave (Años, Proyectos, Verticales) |
| Diferenciación | `#por-que-nosotros` | 3 pilares alineados con servicios reales |
| Pilares | `#pilares` | Tarjetas dinámicas desde BD (`service_categories`) |
| Modelo de Trabajo | `#como-trabajamos` | 4 pasos: Diagnóstico, Arquitectura, Ejecución, Evolución |
| App-CRM Premium | `#app-crm-expanded` | Grilla 6 módulos + Carrusel 4 screenshots |
| Tech Stack | (condicional) | Ticker animado si existen imágenes en `/stack/` |
| CTA Flow | `#contacto` | Flujo guiado: Pilar → Servicio → Plan → Ticket |
| FAQ | `#faq` | 7 preguntas; primera abierta por defecto |
| Partners | (condicional) | Logos si existen en `/socios/` |
| Blog | `#blog` | Últimos posts desde BD |

---

## 12. Changelog v2.0.0 — Rebranding Completo

### Archivos de Vista Modificados
| Archivo | Cambio Aplicado |
|---------|----------------|
| `layouts/admin.php` | `vezetaelea_logo.png` → `logo.png` |
| `layouts/staff.php` | ídem |
| `layouts/client.php` | ídem |
| `admin/budgets/view.php` | ídem |
| `admin/invoices/view.php` | ídem |
| `staff/budgets/view.php` | ídem |
| `staff/invoices/view.php` | ídem |
| `client/budgets/view.php` | ídem |
| `client/invoices/view.php` | ídem |
| `public/home.php` | `#dw-os-showcase` → `#vzl-os-showcase`; `dw_os_*.png` → `vzl_os_*.png` |
| `layouts/public.php` | Links `#dw-os-showcase` → `#vzl-os-showcase` |

### Archivos PHP Modificados
| Archivo | Cambio Aplicado |
|---------|----------------|
| `BudgetController.php` | Prefijo `DW-B` → `VZL-B` |
| `InvoiceService.php` | Prefijo `DW-INV-` → `VZL-INV-` |

### Imágenes Renombradas
| Antes | Después |
|-------|---------|
| `dw_os_crm.png` | `vzl_os_crm.png` |
| `dw_os_ai.png` | `vzl_os_ai.png` |
| `dw_os_finops.png` | `vzl_os_finops.png` |
| `dw_os_realtime.png` | `vzl_os_realtime.png` |

### Archivos SQL Canónicos (nuevos en v2.0)
| Archivo | Descripción |
|---------|-------------|
| `database/schema_vezetaelea.sql` | Esquema completo consolidado v2.0 |
| `database/seed_vezetaelea.sql` | Datos iniciales actualizados v2.0 |

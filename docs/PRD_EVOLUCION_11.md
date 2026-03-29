# Documento de Requisitos de Producto (PRD)
## Evolución 11.0: "Misión Enterprise & SaaS Élite"

---

## 1. Visión Ejecutiva
El objetivo de la **Evolución 11.0** es transformar **VeZetaeLeA OS** de un monolito avanzado en un **SaaS Enterprise** capaz de escalar transversalmente, integrando Inteligencia Artificial Nativa para eficientizar la operación humana, y estandarizando la infraestructura bajo DevOps/FinOps de grado corporativo.

## 2. Definición del Éxito
- 0% de latencia en percepción de notificaciones (WebSockets).
- 100% de fiabilidad en balances contables de clientes (Event Sourcing).
- Despliegue estandarizado de la aplicación en cualquier servidor en < 5 minutos (Docker).
- Reducción del 40% del tiempo de resolución de tickets aprovechando la IA de Copilot GAI.
- Cobertura del 80% en tests de regresión para flujos financieros críticos.

---

## 3. Fases de Implementación y Backlog

La ejecución se dividirá en 4 Sprints principales, abordando desde la infraestructura de desarrollo táctico hasta la modernización de negocio.

### Fase 1 (Sprint 1): Consolidación Estructural & Caché
*Preparar el terreno para las nuevas integraciones grandes.*

| ID | Tarea | Tipo | Prioridad | Criterios de Aceptación | Estado |
|----|-------|------|-----------|------------------------|--------|
| `E11-001` | **Refactorización a Repository Pattern** | Back-End | Crítica | Extraer las llamadas PDO directas en `TicketService` y `InvoiceService` a interfaces de repositorios aislados. | ✅ Finalizado |
| `E11-002` | **Setup Base de Dockerización** | DevOps | Alta | Contenerizar Apache/PHP, MySQL y crear el `docker-compose.yml` maestro para desarrollos estandarizados. | ✅ Finalizado |
| `E11-003` | **Inyección de Dependencias Robusta** | Core | Media | Refinar el instanciador actual de `App\Domain` o introducir un contenedor DI sencillo `Core\Container`. | ✅ Finalizado |
| `E11-004` | **Sistema de Caché Adaptativo** | Back-End | Alta | Implementar capa con `Redis` en `Core\Cache` como wrapper, aplicándolo a endpoints del catálogo de servicios. | ✅ Finalizado |

### Fase 2 (Sprint 2): Módulo GAI (Generative AI)
*Aumento de la capacidad productiva del staff.*

| ID | Tarea | Tipo | Prioridad | Criterios de Aceptación |
|----|-------|------|-----------|------------------------|
| `E11-005` | **Integración de SDK de LLM** | Back-End | Alta | Crear el `AIService` y conectar vía cURL o SDK la API de OpenAI (gpt-4o) / Anthropic al core del sistema, usando el API Key en `.env`. | ✅ Finalizado |
| `E11-006` | **GAI-01: Auto-Summaries de Tickets** | Backend/UI | Alta | Botón "Generar Resumen" en Panel Staff que condensa +15 mensajes en un recuadro de contexto para *handoff* de analistas. | ✅ Finalizado |
| `E11-007` | **GAI-02: Extracción de Action Items** | Backend/UI | Media | AI lee requerimiento inicial en Ticket Creado y sugiere sub-tareas de proyecto en el Workspace automáticamente. | ✅ Finalizado |
| `E11-008` | **GAI-03: Asistente Copilot en Chat** | UI/JS | Baja | Botón en el input del chat para redactar tono formal/ejecutivo rápidamente basado en un borrador *bullet point* del Staff. | ✅ Finalizado |

### Fase 3 (Sprint 3): Reactor en Tiempo Real (WebSockets)
*Experiencia de usuario con fricción cero.*

| ID | Tarea | Tipo | Prioridad | Criterios de Aceptación | Estado |
|----|-------|------|-----------|------------------------|--------|
| `E11-009` | **Integración Servidor WebSocket** | DevOps / BE | Media | Incluir un servidor *loop* asíncrono en Docker (Ej: Ratchet o Swoole) manejado por Supervidor o un Worker PHP largo. | ✅ Finalizado |
| `E11-010` | **Chat Real-Time (Pub/Sub)** | UI / BE | Baja | Reemplazar el *polling* AJAX del Chat por WebSockets y actualizar DOM. | ✅ Finalizado |
| `E11-011` | **Notificaciones Push en Vivo** | UI / BE | Media | Badges, campana y alertas ("Toasts") lanzadas al Cliente apenas cambia el estado de *Ticket* o *Factura*, sin recargar. | ✅ Finalizado |

### Fase 4 (Sprint 4): FinOps Enterprise e Inmutabilidad (Event Sourcing)
*Garantizar cero fallas en contabilidad matemática y DevOps finales.*

| ID | Tarea | Tipo | Prioridad | Criterios de Aceptación | Estado |
|----|-------|------|-----------|------------------------|--------|
| `E11-012` | **Evolución Schema DB a Event Sourcing** | Base de Datos | Alta | Crear tabla maestra `invoice_events` where se guarden los comandos (CREATE, APPLY_PAYMENT, VOID, DISCOUNT). | ✅ Finalizado |
| `E11-013` | **Event Sourcing Processor** | Back-End | Crítica | El cálculo del balance de una Invoice ya no leerá la columna `amount`, sino que derivará del `EventStore`. | ✅ Finalizado |
| `E11-014` | **Dashboard BI & KPIs** | Back-End/UI | Alta | Implementar cálculo de MRR, ARR y Churn Rate con visualización gráfica en el panel admin. | ✅ Finalizado |
| `E11-015` | **Exportación Global CSV** | Back-End/UI | Media | Capacidad de exportar Tickets, Facturas y Proyectos a CSV para portabilidad empresarial. | ✅ Finalizado |

---

## 4. Riesgos y Mitigaciones
- **Complejidad del Event Sourcing:** Tarea E11-012/013. Requiere pruebas extensas ya que romper un flujo de pagos colapsa el negocio. *Mitigación:* Se implementará en entorno paralelo (`finance_v2`) antes de reemplazar la lógica actual.
- **Rendimiento WS:** Tarea E11-009. Los loops en PHP (WebSockets) suelen requerir monitoreo especial por memory leaks. *Mitigación:* Contenedor de Docker se reiniciará automáticamente vía cron interno si cede, y en caso de caída el FE tendrá Fallback automático al polling AJAX tradicional.
- **Costos API GAI:** *Mitigación:* Se usará modelo ligero para resúmenes (Ej. GPT-4o-mini o Claude Haiku) por costo-eficiencia temporal.

## 5. Estrategia de Despliegue
El desarrollo principal se ramificará en `feature/evo-11-core`. Tras superar todas las revisiones QA (Phase 4 Pipelines), se publicará en modo `demo` con flag `GAI_ENABLED=true` y `WS_ENABLED=true` antes de tocar la BD de Producción.

# 📟 Capa API v1 - Documentación Técnica

## 🎯 Objetivo
Proveer una interfaz de comunicación segura, sin estado y de alto rendimiento para aplicaciones externas (Móviles, React dashboards o Third-party integrations).

---

## 🔒 Seguridad (JWT)
La API utiliza **JSON Web Tokens (JWT)** para la autenticación. 
- **Algoritmo**: HS256
- **Cabecera**: `Authorization: Bearer <TOKEN>`
- **Payload**: Incluye `user_id`, `role` y `email`.

### Flujo de Autenticación
1. El cliente envía credenciales a `/api/v1/auth/login`.
2. El servidor valida y devuelve un `token`.
3. El cliente incluye el token en todas las peticiones subsecuentes.

---

## 🛣️ Enrutamiento Especializado
Todas las peticiones a `/api/*` son interceptadas por `Core\App` y delegadas a `Core\ApiRouter`.
- **Bypass CSRF**: Las rutas API no requieren tokens CSRF ya que usan JWT.
- **Formato**: Siempre responde en `application/json`.
- **Versiones**: Soporta versionamiento (actualmente `v1`).

---

## 📡 Endpoints Disponibles

### 1. Autenticación (`auth`)
| Método | Endpoint | Descripción | Requisitos |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/login` | Obtiene un token JWT. | `email`, `password` |

### 2. Proyectos (`projects`)
| Método | Endpoint | Descripción | Requisitos |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/projects` | Lista servicios activos y progreso. | Token JWT |

### 3. Analíticas (`analytics`)
| Método | Endpoint | Descripción | Requisitos |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/analytics/conversions` | Tasas de conversión L->T->I. | Token JWT |
| `GET` | `/api/v1/analytics/financial` | KPIs de Revenue y ARPU. | Token JWT (Admin) |

### 4. Automatización (`automation`)
| Método | Endpoint | Descripción | Requisitos |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/automation/rules` | Lista reglas activas. | Token JWT (Admin) |
| `GET` | `/api/v1/automation/logs` | Historial de ejecuciones. | Token JWT (Admin) |

---

## 🛠️ Implementación para Desarrolladores

### Estructura de Clases
- `Core\ApiRouter`: Parsea y despacha peticiones API.
- `Core\JWT`: Clase estática para codificación/decodificación segura.
- `App\Controllers\Api\ApiController`: Base con helpers `json()` y `error()`.

### Ejemplo de Respuesta Exitosa
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Pipeline ETL Avanzado",
        "progress_percent": 45
    },
    "request_id": "a1b2c3d4"
}
```

### Ejemplo de Error
```json
{
    "success": false,
    "error": "Invalid or expired token",
    "request_id": "e5f6g7h8"
}
```

---

## 🔍 Trazabilidad
Cada respuesta de la API incluye un campo `request_id`. Este ID puede usarse para buscar en los logs del servidor (`storage/logs/security_YYYY-MM-DD.json`) los detalles técnicos de la petición en caso de error.

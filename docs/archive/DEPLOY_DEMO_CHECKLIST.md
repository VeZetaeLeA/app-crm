# ✅ CHECKLIST DE DESPLIEGUE A DEMO

**Proyecto:** DataWyrd  
**PRD:** Estabilización, Gestión de Entornos y Hardening Técnico  
**Fecha:** 09 de Febrero, 2026  
**Versión:** 1.3.0  

---

## 📋 CHECKLIST OBLIGATORIO (PRD Sección 8)

El equipo **NO puede marcar demo como estable** si no se cumple:

### Requisitos Funcionales

- [ ] **RF-01:** `.env` existe en el servidor
- [ ] **RF-01:** `ENVIRONMENT=demo` configurado
- [ ] **RF-02:** Loader de `.env` activo (verificar que `getenv('ENVIRONMENT')` funciona)
- [ ] **RF-02:** Sistema falla con mensaje claro si `.env` no existe
- [ ] **RF-03:** Formato de `.env` limpio (evitar comentarios inline para máxima compatibilidad)

### Requisitos No Funcionales

- [ ] **RNF-01:** Try/catch global implementado (captura Throwable)
- [ ] **RNF-01:** `display_errors=ON` en demo para debugging
- [ ] **RNF-02:** Logs funcionando en `/storage/logs/php-error.log`
- [ ] **RNF-02:** Archivo de log existe y tiene permisos de escritura (666)
- [ ] **RNF-03:** Todas las rutas usan `BASE_PATH` (sin `../`)
- [ ] **RNF-04:** Conexión DB controlada con try/catch
- [ ] **RNF-04:** Errores de DB se loguean correctamente
- [ ] **RNF-05:** Directorio `storage/logs/` tiene permisos 777
- [ ] **RNF-06:** `APP_KEY` válida (32+ caracteres, no es placeholder)
- [ ] **RNF-07:** En production: `display_errors=OFF`

### Verificación Final

- [ ] **No existen errores 500 silenciosos**
- [ ] **Todos los errores se loguean en `php-error.log`**
- [ ] **Stack traces visibles en demo**
- [ ] **Mensajes genéricos en production**

---

## 🔍 PRUEBAS DE VALIDACIÓN

### 1. Prueba de Carga de .env

**Acción:** Acceder a la URL de demo  
**Resultado esperado:** La aplicación carga sin errores de configuración  
**Si falla:** Verificar que `.env` existe y tiene `ENVIRONMENT=demo`

### 2. Prueba de Error Controlado

**Acción:** Provocar un error intencional (ej: llamar a función inexistente)  
**Resultado esperado:**  
- En demo: Stack trace visible en pantalla
- Log escrito en `storage/logs/php-error.log`

**Si falla:** Verificar permisos de `storage/logs/`

### 3. Prueba de Conexión DB

**Acción:** Cambiar temporalmente `DB_PASSWORD` a un valor incorrecto  
**Resultado esperado:**  
- Error descriptivo mostrando host, database, user
- Error logueado
- No error 500 silencioso

**Si falla:** Verificar implementación de RNF-04 en `Database.php`

### 4. Prueba de APP_KEY

**Acción:** Cambiar `APP_KEY` a un valor corto (ej: "test")  
**Resultado esperado:** Error de seguridad con mensaje claro  
**Si falla:** Verificar validación en `index.php`

---

## 🛠️ COMANDOS DE VERIFICACIÓN

### En el servidor (SSH o File Manager):

```bash
# Verificar que .env existe
ls -la .env

# Verificar permisos de logs
ls -la storage/logs/

# Crear directorio de logs si no existe
mkdir -p storage/logs
chmod 777 storage/logs

# Verificar contenido de .env
cat .env | grep ENVIRONMENT

# Ver últimos errores
tail -50 storage/logs/php-error.log
```

---

## 📝 CONFIGURACIÓN .env PARA DEMO

```bash
# ===========================================
# VARIABLE OBLIGATORIA
# ===========================================
ENVIRONMENT=demo

# ===========================================
# APLICACIÓN
# ===========================================
APP_NAME="Data Wyrd OS"
APP_URL=https://vezetaelea.com/demo/datawyrd

# ===========================================
# BASE DE DATOS (Hostinger)
# ===========================================
DB_HOST=localhost
DB_DATABASE=uxxxxx_datawyrd
DB_USERNAME=uxxxxx_datawyrd
DB_PASSWORD=[PASSWORD_REAL]

# ===========================================
# EMAIL
# ===========================================
MAIL_ENABLED=false
MAIL_USERNAME=noreply@datawyrd.com
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@datawyrd.com

# ===========================================
# SEGURIDAD (GENERAR NUEVA CLAVE)
# ===========================================
APP_KEY=[GENERAR_CON: php -r "echo bin2hex(random_bytes(16));"]
```

**IMPORTANTE:**
- ✅ `APP_URL` sin barra final
- ✅ `DB_HOST=localhost` (no IP externa)
- ✅ `APP_KEY` debe ser única (32 caracteres)

---

## ⚠️ ERRORES COMUNES Y SOLUCIONES

### Error: "Configuration Error: Archivo .env no encontrado"

**Causa:** El archivo `.env` no existe en la raíz del proyecto  
**Solución:** Crear `.env` en el mismo nivel que la carpeta `public/`

### Error: "FATAL: ENVIRONMENT no definida en .env"

**Causa:** Falta la línea `ENVIRONMENT=demo` en `.env`  
**Solución:** Añadir `ENVIRONMENT=demo` al inicio del archivo

### Error: "Security Error: Invalid application key"

**Causa:** `APP_KEY` es muy corta o es el valor por defecto  
**Solución:** Generar nueva clave con `php -r "echo bin2hex(random_bytes(16));"`

### Error 500 sin mensaje

**Causa:** Permisos incorrectos en `storage/logs/`  
**Solución:**
```bash
chmod 777 storage/logs
touch storage/logs/php-error.log
chmod 666 storage/logs/php-error.log
```

### Error: "DB Connection Error: Access denied"

**Causa:** Credenciales de base de datos incorrectas  
**Solución:** Verificar `DB_USERNAME` y `DB_PASSWORD` en `.env`

---

## 📊 MÉTRICAS DE ÉXITO

| Métrica | Objetivo | Estado |
|---------|----------|--------|
| Errores 500 silenciosos | 0 | [ ] |
| Tasa de logging | 100% | [ ] |
| Tiempo de diagnóstico | < 5 min | [ ] |
| Stack traces en demo | Visible | [ ] |
| Seguridad en production | Oculto | [ ] |

---

## 🎯 CRITERIOS DE APROBACIÓN

**El despliegue a DEMO se considera exitoso cuando:**

1. ✅ La aplicación carga sin errores
2. ✅ Los errores intencionales se muestran con stack trace
3. ✅ Todos los errores se loguean en `php-error.log`
4. ✅ La conexión a DB muestra errores descriptivos si falla
5. ✅ No existen errores 500 sin información

**Firma de Aprobación:**

- [ ] Desarrollador: _______________  Fecha: _______
- [ ] QA: _______________  Fecha: _______
- [ ] DevOps: _______________  Fecha: _______

---

**Documento generado automáticamente según PRD de Estabilización**  
**Versión:** 1.0  
**Última actualización:** 09/02/2026

# 🔐 Guía de Configuración de Entornos (.env)

## 📋 Resumen Rápido

| Archivo | Propósito | Se sube a Git? | Dónde existe? |
|---------|-----------|----------------|---------------|
| `.env.example` | Plantilla/Documentación | ✅ SÍ | Todos los entornos |
| `.env` | Configuración REAL | ❌ NO | Cada máquina individual |

---

## 🎯 Flujo de Trabajo Correcto

### 1️⃣ En tu Máquina Local (Desarrollo)

**Primera vez que trabajas en el proyecto:**
```bash
# Copiar la plantilla
cp .env.example .env

# Editar con tus valores locales
# ENVIRONMENT=local
# DB_PASSWORD=tu_password_local
# etc.
```

**Ya existe `.env` creado:**
- ✅ Ya tienes el archivo `.env` en tu proyecto
- ✅ Está configurado para `ENVIRONMENT=local`
- ✅ Puedes editarlo según tus necesidades

---

### 2️⃣ En Hostinger (DEMO)

**Opción A: Crear manualmente en el servidor**
```bash
# Conectar por SSH o usar File Manager de Hostinger
nano .env

# Copiar el contenido de .env.example
# Modificar los valores para DEMO:
ENVIRONMENT=demo
APP_URL=https://vezetaelea.com/demo/VeZetaeLeA
MAIL_ENABLED=true
DB_DATABASE=vezetaelea
# etc.
```

**Opción B: Crear localmente y subir (RECOMENDADO)**
```bash
# En tu máquina local, crear un archivo temporal
# Llamado por ejemplo: .env.demo

# Contenido:
ENVIRONMENT=demo
APP_NAME="VeZetaeLeA OS"
APP_URL=https://vezetaelea.com/demo/VeZetaeLeA
MAIL_ENABLED=true
DB_HOST=localhost
DB_DATABASE=vezetaelea
DB_USERNAME=u123456789_admin
DB_PASSWORD=[PASSWORD_HOSTINGER]
MAIL_USERNAME=contacto@vezetaelea.com
MAIL_PASSWORD=[ZOHO_PASSWORD]
MAIL_FROM_ADDRESS=contacto@vezetaelea.com
APP_KEY=[GENERAR_32_CARACTERES]

# Subir vía FTP/SFTP
# Renombrar en el servidor: .env.demo → .env
```

---

### 3️⃣ En Producción (Futuro)

Mismo proceso que DEMO, pero con:
```bash
ENVIRONMENT=production
APP_URL=https://vezetaelea.com
# etc.
```

---

## ⚠️ REGLAS CRÍTICAS

### ✅ LO QUE SÍ DEBES HACER

1. **Mantener `.env.example` actualizado**
   - Cada vez que agregues una nueva variable a `.env`
   - Actualiza también `.env.example` (sin valores sensibles)
   - Súbelo a Git

2. **Usar `.env` para valores reales**
   - Passwords
   - API Keys
   - **APP_URL**: Crucial para que el helper `url()` funcione correctamente en todos los entornos. No debe tener barra diagonal al final.

3. **Verificar `.gitignore`**
   - ✅ Ya está configurado correctamente
   - `.env` está en la lista de ignorados

### ❌ LO QUE NUNCA DEBES HACER

1. **NUNCA subir `.env` a Git**
   - Contiene passwords y datos sensibles
   - Podría exponer tu base de datos

2. **NUNCA poner valores reales en `.env.example`**
   - Es solo una plantilla
   - Se sube al repositorio público

3. **NUNCA usar el mismo `.env` en todos los entornos**
   - Cada entorno tiene su propio `.env`
   - Local ≠ Demo ≠ Production

### 📋 Formato del archivo .env

Para asegurar la máxima compatibilidad y evitar errores de interpretación por parte de PHP:

1. **Evitar comentarios en la misma línea (inline comments):**
   - ❌ `SESSION_LIFETIME=14400 # 4 Horas` (Puede causar errores en versiones antiguas o servidores específicos)
   - ✅ `SESSION_LIFETIME=14400`
   
2. **Usar comentarios en líneas independientes:**
   - ✅ 
     ```env
     # Tiempo de sesión en segundos (4 horas)
     SESSION_LIFETIME=14400
     ```

*Nota: El cargador de VeZetaeLeA ha sido mejorado para limpiar comentarios inline automáticamente, pero se recomienda seguir el formato limpio por seguridad.*

## 🔄 Sincronización entre Entornos

### Cuando cambias código en local y subes a Hostinger:

```bash
# 1. Hacer cambios en tu código
# 2. Commit y push a Git (si usas Git)
git add .
git commit -m "Nuevas funcionalidades"
git push

# 3. En Hostinger:
# - Hacer pull del código
# - NO tocar el archivo .env del servidor
# - El .env de Hostinger permanece intacto con ENVIRONMENT=demo
```

### Cuando agregas una nueva variable de configuración:

```bash
# 1. En local, editar .env
echo "NUEVA_VARIABLE=valor_local" >> .env

# 2. Actualizar .env.example (sin el valor real)
echo "NUEVA_VARIABLE=" >> .env.example

# 3. Subir .env.example a Git
git add .env.example
git commit -m "Añadida variable NUEVA_VARIABLE"
git push

# 4. En Hostinger, editar .env manualmente
nano .env
# Añadir: NUEVA_VARIABLE=valor_demo
```

---

## 📁 Estado Actual de tu Proyecto

### Archivos Creados:
- ✅ `.env` - Configurado para LOCAL
- ✅ `.env.example` - Plantilla actualizada
- ✅ `.gitignore` - Protege `.env`

### Próximos Pasos:

1. **Verificar que `.env` funciona:**
   ```bash
   # Acceder a tu aplicación local
   http://localhost/VeZetaeLeA
   # Debe funcionar sin errores
   ```

2. **Cuando subas a Hostinger:**
   - Crear `.env` en el servidor con `ENVIRONMENT=demo`
   - NO subir tu `.env` local

---

## 🛠️ Comandos Útiles

### Verificar que `.env` no se suba a Git:
```bash
git status
# .env NO debe aparecer en la lista
```

### Ver diferencias entre `.env` y `.env.example`:
```bash
diff .env .env.example
# Te muestra qué valores tienes en .env que no están en .example
```

### Generar APP_KEY segura:
```bash
# En PHP:
php -r "echo bin2hex(random_bytes(16));"
```

---

## 📝 Ejemplo Completo

### `.env.example` (se sube a Git)
```bash
ENVIRONMENT=local
APP_NAME="VeZetaeLeA OS"
APP_URL=
DB_HOST=localhost
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
MAIL_ENABLED=false
MAIL_USERNAME=
MAIL_PASSWORD=
```

### `.env` en LOCAL (NO se sube a Git)
```bash
ENVIRONMENT=local
APP_NAME="VeZetaeLeA OS"
APP_URL=http://localhost/VeZetaeLeA
DB_HOST=localhost
DB_DATABASE=VeZetaeLeA
DB_USERNAME=root
DB_PASSWORD=
MAIL_ENABLED=false
MAIL_USERNAME=contacto@vezetaelea.com
MAIL_PASSWORD=
```

### `.env` en HOSTINGER (NO se sube a Git)
```bash
ENVIRONMENT=demo
APP_NAME="VeZetaeLeA OS"
APP_URL=https://vezetaelea.com/demo/VeZetaeLeA
DB_HOST=localhost
DB_DATABASE=vezetaelea
DB_USERNAME=u123456789_admin
DB_PASSWORD=SuperSecurePassword123!
MAIL_ENABLED=true
MAIL_USERNAME=contacto@vezetaelea.com
MAIL_PASSWORD=ZohoAppPassword456!

# Seguridad API & JWT (NUEVO)
JWT_SECRET=tu_secreto_super_seguro_aqui
JWT_EXPIRE=3600
JWT_ALGO=HS256
```

---

## ✅ Checklist Final

Antes de hacer deploy a DEMO:

- [ ] `.env` existe en local con `ENVIRONMENT=local`
- [ ] `.env.example` está actualizado con todas las variables
- [ ] `.gitignore` incluye `.env`
- [ ] Verificado que `.env` NO aparece en `git status`
- [ ] Preparado archivo `.env` para Hostinger con `ENVIRONMENT=demo`
- [ ] Verificado que todos los valores sensibles están en `.env`, no en el código

---

**Resumen:** El archivo `.env` es personal de cada entorno. El `.env.example` es la documentación compartida.

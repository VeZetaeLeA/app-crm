# 🚀 Guía de Despliegue - VeZetaeLeA OS en Hostinger

**Versión:** 2.0.0  
**Fecha:** 22 de Febrero, 2026  
**Hosting:** Hostinger  
**Email:** Zoho Mail  

---

## 📋 Tabla de Contenidos

1. [Requisitos Previos](#requisitos-previos)
2. [Preparación del Entorno Local](#preparación-del-entorno-local)
3. [Configuración de Hostinger](#configuración-de-hostinger)
4. [Configuración de Base de Datos](#configuración-de-base-de-datos)
5. [Subida de Archivos](#subida-de-archivos)
6. [Configuración de Zoho Mail](#configuración-de-zoho-mail)
7. [Configuración Final](#configuración-final)
8. [Verificación y Pruebas](#verificación-y-pruebas)
9. [Mantenimiento y Monitoreo](#mantenimiento-y-monitoreo)
10. [Solución de Problemas](#solución-de-problemas)

---

## 1. Requisitos Previos

### Servicios Necesarios

- ✅ **Hosting Hostinger** (Plan Business o superior recomendado)
  - PHP 8.0 o superior
  - MySQL 8.0 o superior
  - Acceso SSH (recomendado)
  - Composer (obligatorio para dependencias)
  - SSL/HTTPS habilitado
  
- ✅ **Dominio** (ej: `www.vezetaelea.com`)
  - DNS configurado apuntando a Hostinger
  
- ✅ **Cuenta Zoho Mail** (para envío de emails)
  - Dominio verificado en Zoho
  - Credenciales SMTP configuradas

### Herramientas Locales

- Cliente FTP/SFTP (FileZilla, WinSCP, o similar)
- Cliente SSH (PuTTY para Windows, o terminal nativa)
- Editor de texto (VS Code, Sublime Text, etc.)
- Cliente MySQL (phpMyAdmin, MySQL Workbench, o similar)

---

## 2. Preparación del Entorno Local

### 2.1 Verificar la Aplicación Localmente

Antes de desplegar, asegúrate de que todo funciona correctamente en local:

```bash
# Verificar que no hay errores
# Probar todas las funcionalidades críticas:
# - Login/Logout
# - Creación de tickets
# - Generación de presupuestos
# - Envío de emails (revisar logs)
```

### 2.2 Configurar el Entorno Profesional

VeZetaeLeA utiliza un sistema de gestión de entornos (SaaS Level) basado en el archivo `.env` y configuraciones jerárquicas en la carpeta `/config`.

1. **Copiar el archivo de ejemplo:**
   ```bash
   cp .env.example .env
   ```

2. **Editar `.env` según el entorno de destino:**

   **Para PRODUCCIÓN (`https://vezetaelea.com`):**
   ```env
   # VARIABLE MAESTRA
   ENVIRONMENT=production

   # Aplicación
   APP_NAME="VeZetaeLeA OS"

   # Base de Datos
   DB_HOST=localhost
   DB_DATABASE=vezetaelea
   DB_USERNAME=u123456789_admin
   DB_PASSWORD=TU_PASSWORD_SEGURO_AQUI

   # Email
   MAIL_ENABLED=true
   MAIL_HOST=smtp.zoho.com
   MAIL_PORT=587
   MAIL_ENCRYPTION=tls
   MAIL_USERNAME=contacto@vezetaelea.com
   MAIL_PASSWORD=TU_PASSWORD_ZOHO_AQUI
   MAIL_FROM_ADDRESS=contacto@vezetaelea.com
   MAIL_FROM_NAME="VeZetaeLeA Support"
   ```

   **Para DEMO/PRUEBAS (`https://vezetaelea.com/demo/vezetaelea`):**
   ```env
   ENVIRONMENT=demo
   APP_NAME="VeZetaeLeA OS - Demo"
   DB_DATABASE=vezetaelea_demo
   # ... resto de credenciales
   ```

3. **Entender la Jerarquía de Configuración:**
   El sistema carga automáticamente la configuración en este orden:
   1. `.env` (Carga `ENVIRONMENT` y secretos).
   2. `config/app.php` (Configuración común a todos los entornos).
   3. `config/{ENVIRONMENT}.php` (Overrides específicos: URLs, flags de debug, etc).

   *No es necesario editar archivos `.php` manualmente para cambiar de servidor. El sistema utiliza el helper `url()` para asegurar que todos los enlaces sean relativos a la `APP_URL` configurada.*

3. **Generar clave de aplicación segura:**
   ```bash
   # En PHP, ejecutar:
   php -r "echo bin2hex(random_bytes(32));"
   # Copiar el resultado en APP_KEY
   ```

### 2.3 Optimizar Archivos para Producción (NUEVO)

1. **Instalar Dependencias de Producción**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
   *Esto garantiza que el sistema use el autoloader de Composer optimizado para velocidad.*

2. **Eliminar archivos innecesarios**:
   ```bash
   # Eliminar archivos de desarrollo
   rm -rf .git
   rm -rf tests
   rm -f phpunit.xml
   rm -rf storage/logs/*.log
   ```

---

## 3. Configuración de Hostinger

### 3.1 Acceder al Panel de Hostinger

1. Inicia sesión en [hpanel.hostinger.com](https://hpanel.hostinger.com)
2. Selecciona tu plan de hosting
3. Ve a la sección **"Hosting"**

### 3.2 Configurar PHP

1. Ve a **"Configuración PHP"** o **"PHP Configuration"**
2. Selecciona **PHP 8.0** o superior
3. Habilita las siguientes extensiones:
   - ✅ `pdo_mysql`
   - ✅ `mbstring`
   - ✅ `openssl`
   - ✅ `json`
   - ✅ `fileinfo`
   - ✅ `curl`

4. Ajusta los límites:
   ```ini
   upload_max_filesize = 20M
   post_max_size = 20M
   max_execution_time = 300
   memory_limit = 256M
   ```

### 3.3 Configurar SSL/HTTPS

1. Ve a **"SSL"** en el panel de Hostinger
2. Activa **"SSL Gratuito"** (Let's Encrypt)
3. Espera a que se active (puede tomar 5-15 minutos)
4. Habilita **"Forzar HTTPS"** para redirigir todo el tráfico HTTP a HTTPS

---

## 4. Configuración de Base de Datos

### 4.1 Crear Base de Datos en Hostinger

1. Ve a **"Bases de Datos MySQL"** en hPanel
2. Clic en **"Crear nueva base de datos"**
3. Configurar:
   - **Nombre de BD:** `vezetaelea` (Hostinger añade prefijo automáticamente)
   - **Usuario:** `u123456789_admin`
   - **Contraseña:** Generar una contraseña segura
4. **Guardar credenciales** (las necesitarás para el archivo `.env`)

### 4.2 Importar Esquema de Base de Datos

**Opción A: Usando phpMyAdmin**

1. Ve a **"phpMyAdmin"** en hPanel
2. Selecciona la base de datos creada
3. Ve a la pestaña **"Importar"**
4. Sube los archivos en este orden:
   - `database/schema.sql`
   - `database/seed.sql` (opcional, para datos de prueba)
   - `database/migration_images_and_plans.sql`
   - `database/migration_audit_logs.sql`
   - `database/migration_sync_dictionary.sql` (Sincronización Diccionario - **NUEVO**)
5. Ejecuta cada archivo

**Opción B: Usando SSH (recomendado para archivos grandes)**

```bash
# Conectar por SSH
ssh u123456789@your-server.hostinger.com

# Importar esquema
mysql -u u123456789_admin -p vezetaelea < database/schema.sql
mysql -u u123456789_admin -p vezetaelea < database/seed.sql
mysql -u u123456789_admin -p vezetaelea < database/migration_images_and_plans.sql
mysql -u u123456789_admin -p vezetaelea < database/migration_audit_logs.sql
```

### 4.3 Verificar Importación

```sql
-- Conectar a phpMyAdmin y ejecutar:
SHOW TABLES;
-- Deberías ver todas las tablas: users, tickets, services, etc.

SELECT COUNT(*) FROM users;
-- Debería mostrar al menos 1 usuario (admin)
```

---

## 5. Subida de Archivos

### 5.1 Estructura de Directorios en Hostinger

Hostinger usa la siguiente estructura:
```
/home/u123456789/
├── public_html/          ← Raíz pública (aquí va el contenido de /public)
├── domains/
└── .env                  ← Archivo de configuración (fuera de public_html)
```

### 5.2 Subir Archivos por FTP/SFTP

**Configuración de FileZilla:**
- **Host:** `ftp.vezetaelea.com` o IP del servidor
- **Usuario:** `u123456789`
- **Contraseña:** Tu contraseña de Hostinger
- **Puerto:** 21 (FTP) o 22 (SFTP)

**Estructura de subida:**

1. **Subir archivos del core fuera de public_html:**
   ```
   /home/u123456789/
   ├── App/
   ├── config/
   ├── Core/
   ├── database/
   ├── storage/
   ├── .env
   └── public_html/
       ├── assets/
       ├── storage/ (symlink)
       ├── .htaccess
       └── index.php
   ```

> [!IMPORTANT]
> Hostinger (Linux) es SENSIBLE a mayúsculas. Asegúrate de que las carpetas `Core` y `App` mantengan su primera letra en mayúscula exactamente como en el repositorio.

2. **Contenido de `public_html/`:**
   - Subir todo el contenido de tu carpeta `/public` local
   - Asegúrate de incluir `.htaccess`

### 5.3 Estructura de index.php Profesional

El archivo `public/index.php` ya está preparado para detectar el entorno automáticamente. Si decides subir el core fuera de `public_html` (recomendado), solo asegúrate de que el autoloader apunte correctamente a la carpeta superior.

**Archivo `index.php` (No requiere cambios manuales de URL):**
```php
require_once __DIR__ . '/../Core/Config.php';
Core\Config::load();
// El sistema ya sabe si es local, demo o production gracias al .env
```

### 5.4 Configurar .htaccess (Crítico para Subdirectorios)

Si instalas en un subdirectorio (ej: `/demo/vezetaelea/`), debes ajustar el `RewriteBase` en `public/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /demo/vezetaelea/public/  # Ajustar según tu ruta
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
    
### 5.5 Protección de Storage y Archivos

Es fundamental proteger el directorio de archivos subidos para evitar la ejecución de código malicioso:

1. **Crear `.htaccess` en `/public/storage/`**:
   ```apache
   php_flag engine off
   Options -Indexes
   ```

2. **Verificar permisos de escritura**:
   Asegúrate de que la carpeta `storage/logs/` tenga permisos `775` o `777` para que el sistema pueda generar el archivo `error.log`.

### 5.6 Monitoreo de Errores (Logging)

En entornos `demo` y `production`, los errores no se muestran en pantalla por seguridad. Debes consultarlos en:
`[RAIZ_PROYECTO]/storage/logs/error.log`

*Nota: Si el archivo no existe, el sistema intentará crearlo si tiene permisos de escritura.*
```

---

## 6. Configuración de Zoho Mail

### 6.1 Verificar Dominio en Zoho
(Seguir pasos estándar de Zoho...)

### 6.2 Crear Cuenta de Email y App Password
(Es vital usar **App Passwords** si tienes 2FA activado).

### 6.3 Configurar Correo
Simplemente añade las credenciales al archivo `.env` del servidor. El sistema se encargará del resto:

```env
# En el .env de Hostinger
MAIL_USERNAME=contacto@vezetaelea.com
MAIL_PASSWORD=tu_app_password
MAIL_FROM_ADDRESS=contacto@vezetaelea.com
```

*Nota: El sistema utiliza el driver SMTP automáticamente en entornos `demo` y `production`.*
```

**Nota:** Si usas PHPMailer, debes instalarlo primero:

```bash
# Opción 1: Composer (recomendado)
composer require phpmailer/phpmailer

# Opción 2: Descarga manual
# Descargar desde https://github.com/PHPMailer/PHPMailer
# Subir a /vendor/phpmailer/
```

---

## 7. Configuración Final

### 7.1 Actualizar URLs en la Base de Datos

Conecta a phpMyAdmin y ejecuta:

```sql
-- Actualizar URLs en emails y configuraciones
UPDATE blog_posts 
SET content = REPLACE(content, 'http://localhost/vezetaelea', 'https://www.vezetaelea.com');

-- Verificar configuración
SELECT * FROM users WHERE role = 'admin';
```

### 7.2 Crear Directorios de Storage

```bash
# Por SSH
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p public_html/storage/uploads
chmod -R 777 storage/logs
chmod -R 777 public_html/storage/uploads
```

### 7.3 Configurar Cron Jobs (Opcional)

Si necesitas tareas programadas (ej: envío de reportes, limpieza de logs):

1. Ve a **"Cron Jobs"** en hPanel
2. Añade un nuevo cron job:
   ```bash
   # Ejecutar cada día a las 2 AM
   0 2 * * * /usr/bin/php /home/u123456789/cron/daily_cleanup.php
   ```

---

## 8. Verificación y Pruebas

### 8.1 Checklist de Verificación

- [ ] **Sitio accesible:** `https://www.vezetaelea.com` carga correctamente
- [ ] **SSL activo:** Candado verde en el navegador
- [ ] **Login funcional:** Puedes iniciar sesión con usuario admin
- [ ] **Base de datos conectada:** Dashboard muestra datos correctos
- [ ] **Emails funcionando:** Prueba crear un ticket y verifica que llegue el email
- [ ] **Subida de archivos:** Prueba subir una imagen en el CMS
- [ ] **Responsive:** Verifica en móvil que todo se ve bien
- [ ] **Performance:** Tiempo de carga < 3 segundos

### 8.2 Pruebas Funcionales

1. **Prueba de Login:**
   ```
   Email: admin@vezetaelea.com
   Password: (tu contraseña de admin)
   ```

2. **Prueba de Ticket:**
   - Crear un ticket desde el formulario público
   - Verificar que llegue email de confirmación
   - Verificar que aparezca en el dashboard

3. **Prueba de Email:**
   - Revisar `storage/logs/mail.log`
   - Verificar que los emails se envíen correctamente

### 8.3 Monitoreo de Errores

```bash
# Ver logs de errores PHP
tail -f /home/u123456789/logs/error_log

# Ver logs de email
tail -f /home/u123456789/storage/logs/mail.log
```

---

## 9. Mantenimiento y Monitoreo

### 9.1 Backups Automáticos

**Configurar en Hostinger:**
1. Ve a **"Backups"** en hPanel
2. Habilita **"Backups Automáticos"** (diarios recomendado)
3. Descarga backups manualmente cada semana

**Backup manual de BD:**
```bash
# Por SSH
mysqldump -u u123456789_admin -p vezetaelea > backup_$(date +%Y%m%d).sql
```

### 9.2 Actualización de Contenido

**Actualizar código:**
```bash
# 1. Hacer backup
# 2. Subir nuevos archivos por FTP
# 3. Limpiar caché si existe
rm -rf storage/cache/*
```

### 9.3 Monitoreo de Performance

**Herramientas recomendadas:**
- Google PageSpeed Insights
- GTmetrix
- Uptime Robot (monitoreo de disponibilidad)

### 9.4 Limpieza de Logs

```bash
# Crear script de limpieza: cron/cleanup_logs.php
<?php
$logFile = __DIR__ . '/../storage/logs/mail.log';
if (file_exists($logFile) && filesize($logFile) > 10485760) { // 10MB
    rename($logFile, $logFile . '.' . date('Ymd'));
    touch($logFile);
}
```

---

## 10. Solución de Problemas

### 10.1 Error 500 - Internal Server Error

**Causas comunes:**
- Permisos incorrectos en archivos
- Error en `.htaccess`
- PHP version incompatible

**Solución:**
```bash
# Verificar logs
tail -f ~/logs/error_log

# Verificar permisos
chmod 644 public_html/.htaccess
chmod 644 public_html/index.php

# Verificar PHP version
php -v
```

### 10.2 Base de Datos No Conecta

**Verificar:**
```php
// Crear archivo test_db.php en public_html
<?php
$host = 'localhost';
$db = 'vezetaelea';
$user = 'u123456789_admin';
$pass = 'TU_PASSWORD';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✅ Conexión exitosa!";
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
```

### 10.3 Emails No Se Envían

**Verificar:**
1. Credenciales SMTP correctas en `.env`
2. Puerto 587 abierto en Hostinger
3. Revisar `storage/logs/mail.log`

**Prueba manual:**
```php
// test_email.php
<?php
require 'Core/Config.php';
require 'Core/Mail.php';

use Core\Config;
use Core\Mail;

Config::load();
$result = Mail::send('tu@email.com', 'Test', 'Esto es una prueba');
echo $result ? '✅ Email enviado' : '❌ Error al enviar';
```

### 10.4 Archivos No Se Suben

**Verificar:**
```bash
# Permisos de carpeta uploads
chmod -R 777 public_html/storage/uploads

# Límites PHP
# Editar en hPanel > PHP Configuration:
upload_max_filesize = 20M
post_max_size = 20M
```

### 10.5 CSS/JS No Cargan

**Verificar:**
- Rutas absolutas vs relativas
- HTTPS mixed content
- Cache del navegador

**Solución:**
```html
<!-- Usar rutas absolutas -->
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
```

---

## 📞 Soporte

**Hostinger:**
- Chat 24/7: [hpanel.hostinger.com](https://hpanel.hostinger.com)
- Knowledge Base: [support.hostinger.com](https://support.hostinger.com)

**Zoho Mail:**
- Help Center: [help.zoho.com/portal/en/kb/mail](https://help.zoho.com/portal/en/kb/mail)
- Support: [zoho.com/mail/help](https://www.zoho.com/mail/help/)

---

## ✅ Checklist Final de Despliegue

- [ ] Dominio apuntando a Hostinger
- [ ] SSL/HTTPS activo
- [ ] PHP 8.0+ configurado
- [ ] Base de datos creada e importada
- [ ] Archivos subidos correctamente
- [ ] `.env` configurado con credenciales de producción
- [ ] Zoho Mail configurado y funcionando
- [ ] Permisos de archivos correctos
- [ ] Backups automáticos habilitados
- [ ] Todas las funcionalidades probadas
- [ ] Monitoreo de uptime configurado
- [ ] Documentación actualizada

---

**¡Despliegue completado! 🎉**

Tu aplicación VeZetaeLeA OS está ahora en producción y lista para recibir clientes.

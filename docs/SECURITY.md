# 🔐 Guía de Seguridad y Mejores Prácticas - Data Wyrd OS

**Versión:** 2.5.0  
**Última actualización:** 03 de Marzo, 2026 (Fase 4 - Intelligence & SecOps)  

---

## 📋 Tabla de Contenidos

1. [Configuración Segura](#configuración-segura)
2. [Protección de Datos](#protección-de-datos)
3. [Autenticación y Autorización](#autenticación-y-autorización)
4. [Validación y Sanitización](#validación-y-sanitización)
5. [Protección contra Ataques Comunes](#protección-contra-ataques-comunes)
6. [Gestión de Sesiones](#gestión-de-sesiones)
7. [Seguridad en Producción](#seguridad-en-producción)
8. [Monitoreo y Auditoría](#monitoreo-y-auditoría)
9. [Checklist de Seguridad](#checklist-de-seguridad)

---

## 1. Configuración Segura

### 1.1 Variables de Entorno

✅ **SIEMPRE usar archivo .env para credenciales**

```env
# ❌ NUNCA hacer esto en código
$password = "mi_password_secreto";

# ✅ CORRECTO: Usar .env (Sin comentarios inline)
DB_PASSWORD=mi_password_secreto
```

**Nota:** Evita usar comentarios en la misma línea que los valores (ej: `DB_PASS=123 # mi pass`) para evitar que el intérprete incluya el comentario como parte del valor. El cargador de VeZetaeLeA limpia estos comentarios, pero es mejor evitarlos por compatibilidad.

**Implementación:**

```php
// Cargar configuración (Automático en index.php)
use Core\Config;

// Usar configuración
$password = Config::get('db.pass');
```

### 1.2 Proteger Archivos Sensibles

**En .htaccess:**

```apache
# Proteger .env
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>

# Proteger archivos sensibles
<FilesMatch "\.(ini|log|conf)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Blindaje de Directorios Internos (NUEVO)
En `/App`, `/Core` y `/storage`, se han implementado archivos `.htaccess` con:
```apache
Deny from all
```
Esto garantiza que aunque el ruteo falle, nadie pueda ver el código fuente o los logs directamente.
```
```

**En .gitignore:**

```
.env
config/database.php
storage/logs/*.log
```

### 1.3 Modo Debug

```php
// ❌ NUNCA en producción
APP_DEBUG=true

// ✅ CORRECTO en producción
APP_DEBUG=false
```

**Implementación:**

```php
if (Config::get('debug', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    // Log errors en lugar de mostrarlos (Automático en index.php)
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');
}
```

---

## 2. Protección de Datos

### 2.1 Conexión a Base de Datos

✅ **SIEMPRE usar PDO con prepared statements**

```php
// ❌ VULNERABLE a SQL Injection
$query = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $query);

// ✅ SEGURO con prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$result = $stmt->fetch();
```

### 2.2 Hashing de Contraseñas

✅ **SIEMPRE usar la clase `Core\Auth::hashPassword()` que implementa Argon2id**

```php
// ❌ NUNCA almacenar contraseñas en texto plano
$password = $_POST['password'];

// ❌ NUNCA usar MD5, SHA1 o el wrapper `password_hash` directamente sin opciones
$password = md5($_POST['password']);

// ✅ CORRECTO: Usar el wrapper del core que inyecta Argon2id 
$password = \Core\Auth::hashPassword($_POST['password']);

// El sistema implementa "Upgrades Transparentes"
// Si un hash viejo (bcrypt) pasa el login, en memoria se hashea con Argon2id y se actualiza en base de datos.
```

### 2.3 Encriptación de Datos Sensibles

Para datos sensibles adicionales:

```php
// Encriptar
function encrypt($data, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Desencriptar
function decrypt($data, $key) {
    list($encrypted, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}
```

---

## 3. Autenticación y Autorización

### 3.1 Autenticación Segura

```php
class AuthController extends Controller
{
    public function doLogin()
    {
        // 1. Validar inputs
        $validator = new Validator();
        $validator->validate($_POST, [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            Session::flash('error', 'Credenciales inválidas');
            return $this->redirect('/auth/login');
        }

        // 2. Sanitizar
        $email = Validator::sanitizeEmail($_POST['email']);
        $password = $_POST['password']; // No sanitizar password

        // 3. Rate limiting (implementar)
        if (!RateLimiter::attempt("login:$email", 5, 15)) {
            Session::flash('error', 'Demasiados intentos. Intenta en 15 minutos.');
            return $this->redirect('/auth/login');
        }

        // 4. Autenticar
        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            // 5. Regenerar session ID
            session_regenerate_id(true);
            
            // 6. Guardar usuario en sesión
            Session::set('user', $user);
            
            // 7. Limpiar rate limiter
            RateLimiter::clear("login:$email");
            
            $this->redirectByRole();
        } else {
            Session::flash('error', 'Credenciales incorrectas');
            $this->redirect('/auth/login');
        }
    }
}
```

### 3.2 Control de Acceso mediante Policies (Nivel Enterprise)

✅ **CENTRALIZAR la autorización en clases Policy**

VeZetaeLeA v1.1 utiliza el patrón Policy para evitar lógica de permisos duplicada:

```php
// app/policies/ProjectPolicy.php
public static function canView(array $user, array $project): bool {
    if ($user['role'] === 'admin') return true;
    return $project['client_id'] === $user['id'];
}

// Uso en Controlador
if (!ProjectPolicy::canView(Auth::user(), $project)) {
    AuditService::log('unauthorized_access', ['project_id' => $id], 'WARN');
    die('Acceso denegado');
}
```

### 3.3 Auditoría de Acciones Críticas e Inmutabilidad (Zero Trust)

✅ **REGISTRAR cada cambio significativo en `audit_logs`**

El sistema cuenta con un motor criptográfico (Blockchain Style). Cada registro auditable obtiene la firma (hash) del acceso anterior y genera un SHA256 bloqueando el historial para evidenciar si existe manipulación de base de datos directamente a fuerza bruta o a través de accesos de administrador.

```php
// En el controlador/servicio
AuditService::log('invoice_paid', [
    'invoice_id' => $id,
    'amount' => $total,
    'method' => 'stripe'
]);
// Internamente generará el `signature_hash` y será imposible de alterar inadvertidamente.
```

### 3.3 Timeout de Sesión

El timeout se gestiona centralizadamente en `Core\Session::start()`. No es necesario añadir lógica de manual en los controladores o en `index.php`.

```php
// Configuración recomendada en .env
SESSION_LIFETIME=7200 # 2 horas
```

El sistema utiliza automáticamente `session_set_cookie_params()` y maneja el controlador de base de datos para asegurar que las sesiones expiren correctamente tanto en el cliente (cookie) como en el servidor (DB).

---

## 4. Validación y Sanitización

### 4.1 Validación de Inputs

✅ **SIEMPRE validar TODOS los inputs del usuario**

```php
use Core\Validator;

// Ejemplo completo
$validator = new Validator();
$validator->validate($_POST, [
    'name' => 'required|min:3|max:100|alpha',
    'email' => 'required|email',
    'age' => 'numeric',
    'website' => 'url',
    'role' => 'in:admin,staff,client'
]);

if ($validator->fails()) {
    // Manejar errores
    $errors = $validator->errors();
    Session::flash('errors', $errors);
    return $this->redirect('/form');
}
```

### 4.2 Sanitización de Outputs

✅ **SIEMPRE escapar outputs en vistas**

```php
// ❌ VULNERABLE a XSS
<p><?= $user['name'] ?></p>

// ✅ SEGURO
<p><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></p>

// O crear helper
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

<p><?= e($user['name']) ?></p>
```

### 4.3 Validación de Archivos

```php
// Validar archivo subido
$errors = Validator::validateFile($_FILES['document'], 
    10485760, // 10MB max
    ['pdf', 'doc', 'docx', 'xls', 'xlsx']
);

if (!empty($errors)) {
    Session::flash('error', implode(', ', $errors));
    return $this->redirect('/upload');
}

// Generar nombre seguro
$extension = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
$filename = bin2hex(random_bytes(16)) . '.' . $extension;

// Mover a ubicación segura
$uploadPath = __DIR__ . '/../storage/uploads/' . $filename;
move_uploaded_file($_FILES['document']['tmp_name'], $uploadPath);
```

---

## 5. Protección contra Ataques Comunes

### 5.1 SQL Injection

✅ **Protección implementada con PDO**

```php
// ✅ SEGURO
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

// ✅ SEGURO con named parameters
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = :status");
$stmt->execute(['email' => $email, 'status' => 'active']);
```

### 5.2 Cross-Site Scripting (XSS)

✅ **Protección con sanitización**

```php
// En inputs
$name = Validator::sanitizeString($_POST['name']);

// En outputs (vistas)
<p><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></p>

// Para HTML rico (usar librería como HTML Purifier)
$clean = HTMLPurifier::purify($dirtyHtml);
```

### 5.3 Cross-Site Request Forgery (CSRF)

✅ **Protección implementada con tokens**

```php
// Generar token en formulario (Helper)
<form method="POST" action="/ticket/submit">
    <?= csrf_field() ?>
    <!-- campos -->
</form>

// La verificación es AUTOMÁTICA en el constructor de Core\App.php
```

### 5.4 Clickjacking

```apache
# En .htaccess
Header always set X-Frame-Options "SAMEORIGIN"
Header always set Content-Security-Policy "frame-ancestors 'self'"
```

### 5.5 Session Hijacking

```php
// Regenerar session ID después de login
session_regenerate_id(true);

// Usar cookies seguras en producción (Configurado en Core\Session::start())
if (Config::get('ENVIRONMENT') === 'production') {
    // Configurado vía session_set_cookie_params
    // Secure: true, HttpOnly: true, SameSite: Lax/Strict
}
```

---

## 6. Gestión de Sesiones

### 6.1 Configuración Segura

```php
// En public/index.php antes de Session::start()
if (Config::get('APP_ENV') === 'production') {
    ini_set('session.cookie_secure', 1);      // Solo HTTPS
    ini_set('session.cookie_httponly', 1);    // No accesible por JS
    ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
    ini_set('session.use_strict_mode', 1);    // Rechazar IDs no inicializados
}
```

### 6.2 Limpieza de Sesiones

```php
// Logout seguro
public function logout()
{
    // Destruir sesión
    Session::destroy();
    
    // Limpiar cookie de sesión
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    $this->redirect('/');
}
```

---

## 7. Seguridad en Producción

### 7.1 Configuración de Servidor

**Apache (.htaccess):**

```apache
# Deshabilitar listado de directorios
Options -Indexes

# Proteger archivos sensibles
<FilesMatch "\.(env|log|ini|conf)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Headers de seguridad
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"

# Forzar HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 7.2 Permisos de Archivos

```bash
# Archivos
find . -type f -exec chmod 644 {} \;

# Directorios
find . -type d -exec chmod 755 {} \;

# Archivos sensibles
chmod 600 .env
chmod 600 config/*.php

# Directorios escribibles
chmod -R 777 storage/logs
chmod -R 777 public/storage/uploads
```

### 7.3 Actualización de Dependencias

```bash
# Verificar versiones de PHP y MySQL
php -v
mysql --version

# Mantener sistema actualizado
sudo apt update && sudo apt upgrade
```

---

## 8. Seguridad en API (JWT)

### 8.1 Autenticación sin Estado
La API v1 de Data Wyrd utiliza **JSON Web Tokens (JWT)** para eliminar la dependencia de sesiones en servidor.

✅ **Implementación Segura (`Core\JWT`)**:
- **Bypass CSRF**: Los endpoints `/api/*` ignoran el token CSRF para permitir consumo cross-domain controlado.
- **Validación de Firma**: Cada token es firmado con una `JWT_SECRET` única definida en el `.env`.
- **Expiración**: El sistema impone tiempos de vida cortos para tokens de acceso.

```php
// Ejemplo de validación en ApiController
$token = JWT::getBearerToken();
$payload = JWT::decode($token);
if (!$payload) {
    return $this->error('Token inválido', 401);
}
```

---

## 9. Monitoreo, Auditoría y Trazabilidad

### 9.1 Trazabilidad Universal (Request ID)
Para facilitar la auditoría forense, cada petición al sistema genera un **Request ID** único de 32 caracteres.

```php
// Acceso global
$requestId = App::$requestId;
```

Este ID se inyecta automáticamente en:
- Respuestas HTTP (`X-Request-ID` header).
- Respuestas JSON de la API.
- Tablas de Auditoría (`audit_logs.request_id`).
- Archivos de Log JSON.

### 9.2 Security Logging Estructurado (JSON)
El `SecurityLogger` ahora genera archivos JSON diarios compatibles con herramientas de observabilidad.

```json
{
  "timestamp": "2026-02-22T20:00:00Z",
  "request_id": "a1b2c3d4...",
  "event": "unauthorized_api_access",
  "user": "guest",
  "ip": "192.168.1.1",
  "details": {
    "endpoint": "/api/v1/projects",
    "method": "GET"
  }
}
```
Ubica estos logs en: `/storage/logs/security_YYYY-MM-DD.json`.

---

## 10. Checklist de Seguridad

### Pre-Producción

- [ ] `.env` configurado con credenciales de producción
- [ ] `APP_DEBUG=false` en producción
- [ ] Contraseñas por defecto cambiadas
- [ ] SSL/HTTPS habilitado y forzado
- [ ] Headers de seguridad configurados
- [ ] Permisos de archivos correctos (644/755)
- [ ] `.env` y archivos sensibles en `.gitignore`
- [ ] Protección CSRF implementada en formularios
- [ ] Validación de inputs en todos los formularios
- [ ] Rate limiting en login
- [ ] Session timeout configurado
- [ ] Cookies seguras habilitadas

### Post-Producción

- [ ] Backups automáticos configurados
- [ ] Monitoreo de logs activo
- [ ] Alertas de seguridad configuradas
- [ ] Firewall configurado
- [ ] Actualizaciones de seguridad aplicadas
- [ ] Pruebas de penetración realizadas
- [ ] Plan de respuesta a incidentes documentado

### Mantenimiento Regular

- [ ] Revisar logs de seguridad semanalmente
- [ ] Actualizar dependencias mensualmente
- [ ] Rotar contraseñas cada 90 días
- [ ] Auditar permisos de usuarios
- [ ] Verificar backups funcionan
- [ ] Revisar y actualizar políticas de seguridad

---

## 📞 Reporte de Vulnerabilidades

Si encuentras una vulnerabilidad de seguridad, por favor repórtala a:

**Email:** contacto@vezetaelea.com  
**Tiempo de respuesta:** 24-48 horas

**NO** publiques vulnerabilidades públicamente hasta que sean corregidas.

---

## 📚 Recursos Adicionales

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [MySQL Security Best Practices](https://dev.mysql.com/doc/refman/8.0/en/security.html)

---

**Última actualización:** Febrero 2026  
**Próxima revisión:** Mayo 2026

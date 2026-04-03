<?php
namespace Core;

/**
 * Input Validation and Sanitization Helper
 */
class Validator
{
    private $errors = [];
    private $data = [];

    /**
     * Validate input data
     * 
     * @param array $data Input data to validate
     * @param array $rules Validation rules
     * @return self
     */
    public function validate($data, $rules)
    {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $ruleList = explode('|', $ruleSet);
            $value = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return $this;
    }

    /**
     * Apply a single validation rule
     */
    private function applyRule($field, $value, $rule)
    {
        // Parse rule with parameters (e.g., "max:255")
        $params = [];
        if (strpos($rule, ':') !== false) {
            list($rule, $paramString) = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, "El campo $field es obligatorio.");
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "El campo $field debe ser un email válido.");
                }
                break;

            case 'min':
                $min = $params[0] ?? 0;
                if (strlen($value) < $min) {
                    $this->addError($field, "El campo $field debe tener al menos $min caracteres.");
                }
                break;

            case 'max':
                $max = $params[0] ?? 255;
                if (strlen($value) > $max) {
                    $this->addError($field, "El campo $field no puede exceder $max caracteres.");
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->addError($field, "El campo $field debe ser numérico.");
                }
                break;

            case 'alpha':
                if ($value && !ctype_alpha(str_replace(' ', '', $value))) {
                    $this->addError($field, "El campo $field solo puede contener letras.");
                }
                break;

            case 'alphanumeric':
                if ($value && !ctype_alnum(str_replace(' ', '', $value))) {
                    $this->addError($field, "El campo $field solo puede contener letras y números.");
                }
                break;

            case 'url':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "El campo $field debe ser una URL válida.");
                }
                break;

            case 'in':
                if ($value && !in_array($value, $params)) {
                    $this->addError($field, "El valor de $field no es válido.");
                }
                break;
        }
    }

    /**
     * Add error message
     */
    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Check if validation passed
     */
    public function passes()
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails()
    {
        return !$this->passes();
    }

    /**
     * Get all errors
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get first error for a field
     */
    public function first($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Sanitize string input
     */
    public static function sanitizeString($input)
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail($email)
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize URL
     */
    public static function sanitizeUrl($url)
    {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize integer
     */
    public static function sanitizeInt($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Generate CSRF Token
     */
    public static function generateCsrfToken()
    {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    /**
     * Verify CSRF Token
     */
    public static function verifyCsrfToken($token)
    {
        $sessionToken = Session::get('csrf_token');
        return $sessionToken && hash_equals($sessionToken, $token);
    }

    /**
     * Validate file upload
     */
    public static function validateFile($file, $maxSize = 10485760, $allowedExtensions = [])
    {
        $errors = [];

        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = 'Parámetros de archivo inválidos.';
            return $errors;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al subir el archivo.';
            return $errors;
        }

        // 1. Validar tamaño
        if ($file['size'] > $maxSize) {
            $errors[] = 'El archivo excede el tamaño máximo permitido.';
        }

        // 2. Validar Tipo MIME Real (No solo extensión)
        $mimeType = 'application/octet-stream';
        if (function_exists('finfo_open')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detected = $finfo->file($file['tmp_name']);
            if ($detected !== false) {
                $mimeType = $detected;
            }
        }

        // Mapa completo de extensiones a MIME types (incluye variantes reales de libmagic en Linux)
        $mimeMap = [
            'jpg'  => ['image/jpeg', 'image/jpg', 'image/pjpeg'],
            'jpeg' => ['image/jpeg', 'image/jpg', 'image/pjpeg'],
            'png'  => ['image/png', 'image/x-png', 'image/vnd.mozilla.apng'],
            'gif'  => ['image/gif'],
            'webp' => ['image/webp'],
            'svg'  => ['image/svg+xml', 'image/svg', 'text/html', 'text/plain'],
            'pdf'  => ['application/pdf', 'application/x-pdf'],
            'zip'  => ['application/zip', 'application/x-zip', 'application/x-zip-compressed', 'application/octet-stream'],
            'zipx' => ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'],
            'doc'  => ['application/msword', 'application/octet-stream'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/octet-stream', 'application/zip'],
            'xls'  => ['application/vnd.ms-excel', 'application/octet-stream'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/octet-stream', 'application/zip'],
        ];

        if (!empty($allowedExtensions)) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                $errors[] = "Extensión .$ext no permitida.";
            } else {
                // Verificar que el MIME coincida con la extensión esperada
                $expectedMimes = $mimeMap[$ext] ?? [];

                // Si finfo devuelve octet-stream y la extensión es válida, lo aceptamos
                // (algunos servidores no tienen la firma binaria del formato en su base de datos)
                $isOctetStream = ($mimeType === 'application/octet-stream');
                $isImageExt    = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp']);

                if (!empty($expectedMimes) && !in_array($mimeType, $expectedMimes)) {
                    // Sólo bloqueamos si finfo pudo identificar claramente un MIME distinto al esperado
                    // y no es una imagen con octet-stream (que ocurre en algunos entornos Linux)
                    if (!($isOctetStream && $isImageExt)) {
                        $errors[] = "Contenido del archivo no coincide con su extensión (Falsificación de tipo).";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Generar nombre de archivo seguro (Hash + Timestamp)
     */
    public static function generateSecureFileName($originalName)
    {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return md5(uniqid(microtime(), true)) . '_' . time() . '.' . $ext;
    }
}

<?php
namespace App\Services;

class AIService
{
    private $apiKey;
    private $model;
    private $provider;
    private $endpoint;

    public function __construct()
    {
        // Obtain from configuration architecture with fallback to legacy keys if needed.
        $this->provider = \Core\Config::get('app.ai_provider') ?: (getenv('AI_PROVIDER') ?: 'openai');
        $this->apiKey   = \Core\Config::get('app.ai_api_key') ?: (getenv('AI_API_KEY') ?: (getenv('OPENAI_API_KEY') ?: null));
        $this->model    = \Core\Config::get('app.ai_model') ?: (getenv('AI_MODEL') ?: (getenv('OPENAI_MODEL') ?: 'gpt-4o-mini'));

        // Set dynamic endpoint based on provider.
        if ($this->provider === 'groq') {
            $this->endpoint = 'https://api.groq.com/openai/v1/chat/completions';
        } else {
            $this->endpoint = 'https://api.openai.com/v1/chat/completions';
        }
    }

    public function isEnabled(): bool
    {
        return !empty($this->apiKey);
    }

    public function query(array $messages, float $temperature = 0.7, bool $jsonMode = false)
    {
        if (!$this->isEnabled()) {
            return ['error' => 'API Key no configurada'];
        }

        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
        ];

        if ($jsonMode) {
            $data['response_format'] = ['type' => 'json_object'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || !$response) {
            error_log("AI API Error ({$this->provider}): HTTP " . $httpCode . " Response: " . $response);
            return ['error' => "Error al contactar con la API de {$this->provider}. Código: " . $httpCode];
        }

        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'] ?? null;
    }

    /**
     * E11-006: GAI-01 - Genera un sumario del caso basado en el historial del chat
     */
    public function generateTicketSummary(array $messages): ?string
    {
        $context = "Resume muy brevemente los siguientes mensajes de un ticket de soporte de manera ejecutiva y estructurada (Problema, Acciones Tomadas, Pendientes):\n\n";
        foreach ($messages as $msg) {
            $role = !empty($msg['is_admin']) ? 'Staff' : 'Cliente';
            $date = $msg['created_at'] ?? '';
            $msgContent = $msg['message'] ?? '';
            $context .= "{$role} ({$date}): {$msgContent}\n";
        }

        $prompt = [
            ['role' => 'system', 'content' => 'Eres un asistente experto en soporte técnico B2B, encargado de resumir tickets complejos para handoff entre analistas. Devuelve solo el resumen en Markdown usando tres secciones: **Problema**, **Acciones Tomadas** y **Pendientes**.'],
            ['role' => 'user', 'content' => $context]
        ];

        $result = $this->query($prompt, 0.3);
        return is_array($result) && isset($result['error']) ? null : trim($result);
    }

    /**
     * E11-007: GAI-02 - Extrae items de acción
     */
    public function extractActionItems(string $description): ?array
    {
        $prompt = [
            ['role' => 'system', 'content' => 'Eres un Technical Project Manager. Extrae una lista de tareas (action items) accionables a partir del requerimiento inicial de un cliente. Devuelve ÚNICAMENTE un JSON array válido de strings (ej: ["Configurar BD", "Revisar logs"]), sin explicaciones ni markdown extra.'],
            ['role' => 'user', 'content' => "Requerimiento: {$description}"]
        ];

        $result = $this->query($prompt, 0.2);
        if (is_array($result) && isset($result['error'])) {
            return null;
        }

        $result = preg_replace('/```json|```/', '', $result);
        $items = json_decode(trim($result), true);
        
        return is_array($items) ? $items : null;
    }

    /**
     * E11-008: GAI-03 - Reescribe borrador a tono formal/ejecutivo
     */
    public function rewriteDraft(string $draft, string $tone = 'formal y profesional'): ?string
    {
        $prompt = [
            ['role' => 'system', 'content' => "Eres un asistente de redacción para ejecutivos de soporte técnico. Debes reescribir el borrador del usuario con un tono {$tone}, directo y al punto."],
            ['role' => 'user', 'content' => $draft]
        ];

        $result = $this->query($prompt, 0.4);
        return is_array($result) && isset($result['error']) ? null : trim($result);
    }

    /**
     * E11-009: GAI-04 - Análisis de sentimiento y prioridad sugerida
     */
    public function analyzeTicketContent(string $content): ?array
    {
        $prompt = [
            ['role' => 'system', 'content' => 'Eres un analista de soporte técnico experto. Tu tarea es analizar el sentimiento del cliente y sugerir una prioridad para el ticket. Devuelve ÚNICAMENTE un JSON válido con estas llaves: "sentiment" (angry, neutral, happy), "priority" (critical, high, normal, low), "summary" (una línea concisa) y "detected_problem" (el problema técnico principal).'],
            ['role' => 'user', 'content' => "Contenido del Ticket: {$content}"]
        ];

        $result = $this->query($prompt, 0.2);
        if (is_array($result) && isset($result['error'])) {
            return null;
        }

        $result = preg_replace('/```json|```/', '', $result);
        $analysis = json_decode(trim($result), true);
        
        return is_array($analysis) ? $analysis : null;
    }

    /**
     * E11-010: GAI-05 - Sugerencia de respuesta basada en contexto
     */
    public function suggestResponse(array $messages, string $initialDescription = ''): ?string
    {
        $context = "Requerimiento Original: {$initialDescription}\n\n";
        $context .= "Historial reciente del Chat:\n";
        
        $relevant = array_slice($messages, -5);
        if (empty($relevant) && empty($initialDescription)) {
            return null; // Todo vacío
        }

        foreach ($relevant as $msg) {
            $role = !empty($msg['is_admin']) ? 'Staff' : 'Cliente';
            $context .= "{$role}: {$msg['message']}\n";
        }

        $prompt = [
            ['role' => 'system', 'content' => 'Eres un agente de soporte senior de VeZetaeLeA OS. Genera una respuesta empática, profesional y técnica basada en el requerimiento y el historial. La respuesta debe ser corta, directa y dirigida al cliente.'],
            ['role' => 'user', 'content' => $context]
        ];

        $result = $this->query($prompt, 0.5);
        return is_array($result) && isset($result['error']) ? null : trim($result);
    }

    /**
     * GAI-06 - Genera estructura JSON estricta (útil para Instagram)
     */
    public function generateStructuredJSON(array $prompt, float $temperature = 0.7): ?array
    {
        $result = $this->query($prompt, $temperature, true);
        if (is_array($result) && isset($result['error'])) {
            return null;
        }

        $result = preg_replace('/```json|```/', '', $result);
        $parsed = json_decode(trim($result), true);
        
        return is_array($parsed) ? $parsed : null;
    }
}

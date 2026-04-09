<?php
namespace App\Services;

use Core\Database;
use Exception;
use PDO;

class InstagramService
{
    private PDO $db;
    private AIService $aiService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->aiService = new AIService();
    }

    /**
     * Obtiene todos los calendarios
     */
    public function getAllCalendars()
    {
        $stmt = $this->db->query("SELECT * FROM instagram_calendar ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un calendario por ID
     */
    public function getCalendar($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM instagram_calendar WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $calendar = $stmt->fetch();

        if ($calendar) {
            $stmt = $this->db->prepare("SELECT * FROM instagram_posts WHERE calendar_id = :id ORDER BY FIELD(day_of_week, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')");
            $stmt->execute([':id' => $id]);
            $calendar['posts'] = $stmt->fetchAll();
        }

        return $calendar;
    }

    /**
     * Genera un nuevo calendario semanal
     */
    public function generateWeeklyCalendar($startDate, $weekLabel, $objective = 'Desconocido')
    {
        try {
            $this->db->beginTransaction();

            // 1. Crear el calendario
            $stmt = $this->db->prepare("INSERT INTO instagram_calendar (week_label, start_date, status) VALUES (:week_label, :start_date, 'draft')");
            $stmt->execute([
                ':week_label' => $weekLabel,
                ':start_date' => $startDate
            ]);
            $calendarId = $this->db->lastInsertId();

            // 2. Definir los pilares y formatos (Estrategia por defecto)
            $strategy = [
                ['day' => 'Lunes', 'pilar' => 'Arquitectura de Datos', 'format' => 'Carrusel'],
                ['day' => 'Martes', 'pilar' => 'Transformación Inteligente', 'format' => 'Post'],
                ['day' => 'Miércoles', 'pilar' => 'Soluciones en Producción', 'format' => 'Post'],
                ['day' => 'Jueves', 'pilar' => 'Arquitectura de Datos', 'format' => 'Post'],
                ['day' => 'Viernes', 'pilar' => 'Transformación Inteligente', 'format' => 'Post'],
                ['day' => 'Sábado', 'pilar' => 'Soluciones en Producción', 'format' => 'Stories/Post'],
                ['day' => 'Domingo', 'pilar' => 'Branding / Comunidad', 'format' => 'Post']
            ];

            $usedTitles = [];

            // 3. Generar contenido para cada post
            foreach ($strategy as $index => $item) {
                $publishDate = date('Y-m-d', strtotime($startDate . " + $index days"));

                $content = $this->generatePostContent($item['pilar'], $item['format'], $objective, $usedTitles);
                if ($content) {
                    $usedTitles[] = $content['title'];
                } else {
                    // Fallback
                    $content = [
                        'title' => 'Post ' . $item['pilar'],
                        'copy' => 'Próximamente...',
                        'cta' => 'Comenta',
                        'hashtags' => '#tech',
                        'prompt' => '',
                        'scenes' => null
                    ];
                }

                $scenesJson = isset($content['scenes']) ? json_encode($content['scenes']) : null;

                $stmt = $this->db->prepare("INSERT INTO instagram_posts 
                    (calendar_id, day_of_week, publish_date, publish_time, strategic_pilar, post_format, internal_title, copy_text, cta_text, hashtags, visual_prompt, scenes_data) 
                    VALUES (:calendar_id, :day, :date, :time, :pilar, :format, :title, :copy, :cta, :hashtags, :prompt, :scenes)");

                $stmt->execute([
                    ':calendar_id' => $calendarId,
                    ':day' => $item['day'],
                    ':date' => $publishDate,
                    ':time' => '11:00:00', // Default B2B time
                    ':pilar' => $item['pilar'],
                    ':format' => $item['format'],
                    ':title' => $content['title'],
                    ':copy' => $content['copy'],
                    ':cta' => $content['cta'],
                    ':hashtags' => $content['hashtags'],
                    ':prompt' => $content['prompt'],
                    ':scenes' => $scenesJson
                ]);
            }

            $this->db->commit();
            return $calendarId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error generating instagram calendar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Utiliza LLM para crear el contenido estratégico estructurado en JSON
     */
    private function generatePostContent($pilar, $format, $objective, $excludeTitles = [])
    {
        $businessName = \Core\Config::get('business.company_name') ?: 'Nuestra Empresa';
        
        $systemPrompt = "Eres un Director Creativo experto en neuro-copywriting B2B y diseño corporativo (Cyberpunk elegante / Glassmorphism B2B) para la empresa $businessName.
Tu objetivo estratégico para esta pieza es: $objective.
El pilar temático es: $pilar.
El formato del post es: $format.

Si el formato es 'Post' o 'Stories/Post':
Devuelve un JSON estrictamente así:
{
  \"tipo\": \"standard\",
  \"internal_title\": \"Título interno corto del post\",
  \"copy_text\": \"El cuerpo del post optimizado (caption largo con saltos de línea)\",
  \"cta_text\": \"Llamado a la acción claro\",
  \"hashtags\": \"#hashtag1 #hashtag2\",
  \"visual_prompt\": \"Prompt detallado cinematográfico en INGLES para la imagen generativa\",
  \"scenes\": null
}

Si el formato es 'Carrusel' o 'Reel':
Devuelve un JSON estrictamente así:
{
  \"tipo\": \"sequence\",
  \"internal_title\": \"Título interno corto de la secuencia\",
  \"copy_text\": \"Caption descriptivo para poner en Instagram resumidamente\",
  \"cta_text\": \"Call to action final\",
  \"hashtags\": \"#hashtag1 #hashtag2\",
  \"visual_prompt\": \"Prompt de estilo general para todas las escenas\",
  \"scenes\": [
    {\"slide_number\": 1, \"text_overlay\": \"Texto gancho slide 1\", \"visual_prompt\": \"Prompt específico slide 1 en Ingles\"},
    {\"slide_number\": 2, \"text_overlay\": \"Texto valor slide 2\", \"visual_prompt\": \"Prompt específico slide 2 en Ingles\"}
  ]
}";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Genera el contenido. Excluye variaciones muy parecidas a estos títulos: " . implode(", ", $excludeTitles)]
        ];

        $response = $this->aiService->generateStructuredJSON($messages, 0.6);

        if (!$response) return null;

        return [
            'title' => $response['internal_title'] ?? 'Sin Título',
            'copy' => $response['copy_text'] ?? '',
            'cta' => $response['cta_text'] ?? '',
            'hashtags' => $response['hashtags'] ?? '',
            'prompt' => $response['visual_prompt'] ?? '',
            'scenes' => $response['scenes'] ?? null
        ];
    }

    /**
     * Regenera un post específico con contenido nuevo
     */
    public function regeneratePost($postId)
    {
        $stmt = $this->db->prepare("SELECT * FROM instagram_posts WHERE id = :id");
        $stmt->execute([':id' => $postId]);
        $oldPost = $stmt->fetch();

        if (!$oldPost)
            return false;

        $newContent = $this->generatePostContent($oldPost['strategic_pilar'], $oldPost['post_format'], 'Maximizar engagement y valor aportado', [$oldPost['internal_title']]);

        $scenesJson = isset($newContent['scenes']) ? json_encode($newContent['scenes']) : null;

        $sql = "UPDATE instagram_posts SET 
                internal_title = :title, 
                copy_text = :copy, 
                cta_text = :cta, 
                hashtags = :hashtags, 
                visual_prompt = :prompt,
                scenes_data = :scenes
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $newContent['title'],
            ':copy' => $newContent['copy'],
            ':cta' => $newContent['cta'],
            ':hashtags' => $newContent['hashtags'],
            ':prompt' => $newContent['prompt'],
            ':scenes' => $scenesJson,
            ':id' => $postId
        ]);
    }

    /**
     * Actualiza un post específico
     */
    public function updatePost($postId, $data)
    {
        $sql = "UPDATE instagram_posts SET 
                internal_title = :title, 
                copy_text = :copy, 
                cta_text = :cta, 
                hashtags = :hashtags, 
                visual_prompt = :prompt,
                publish_date = :date,
                publish_time = :time
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['internal_title'],
            ':copy' => $data['copy_text'],
            ':cta' => $data['cta_text'],
            ':hashtags' => $data['hashtags'],
            ':prompt' => $data['visual_prompt'],
            ':date' => $data['publish_date'],
            ':time' => $data['publish_time'],
            ':id' => $postId
        ]);
    }

    /**
     * Genera el contenido CSV para un calendario
     */
    public function exportCalendarToCsv($calendarId)
    {
        $calendar = $this->getCalendar($calendarId);
        if (!$calendar)
            return "";

        $output = fopen('php://memory', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

        fputcsv($output, [
            'Día',
            'Fecha Publicación',
            'Hora',
            'Pilar Estratégico',
            'Formato',
            'Título Interno',
            'Copy (Instagram Caption)',
            'CTA',
            'Hashtags',
            'Visual Prompt'
        ], ';');

        foreach ($calendar['posts'] as $post) {
            fputcsv($output, [
                $post['day_of_week'],
                $post['publish_date'],
                $post['publish_time'],
                $post['strategic_pilar'],
                $post['post_format'],
                $post['internal_title'],
                $post['copy_text'],
                $post['cta_text'],
                $post['hashtags'],
                $post['visual_prompt']
            ], ';');
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Finaliza un calendario
     */
    public function finalizeCalendar($id)
    {
        $stmt = $this->db->prepare("UPDATE instagram_calendar SET status = 'finalized' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Elimina un calendario
     */
    public function deleteCalendar($id)
    {
        $stmt = $this->db->prepare("DELETE FROM instagram_calendar WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}

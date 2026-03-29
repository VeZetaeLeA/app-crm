<?php
namespace App\Services;

use Core\Database;
use Exception;
use PDO;

class InstagramService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
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
    public function generateWeeklyCalendar($startDate, $weekLabel)
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

                $content = $this->generatePostContent($item['pilar'], $item['format'], $usedTitles);
                $usedTitles[] = $content['title'];

                $stmt = $this->db->prepare("INSERT INTO instagram_posts 
                    (calendar_id, day_of_week, publish_date, publish_time, strategic_pilar, post_format, internal_title, copy_text, cta_text, hashtags, visual_prompt) 
                    VALUES (:calendar_id, :day, :date, :time, :pilar, :format, :title, :copy, :cta, :hashtags, :prompt)");

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
                    ':prompt' => $content['prompt']
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
     * Simula la creación de contenido estratégico (Esto sería idealmente un llamado a una API de LLM)
     */
    private function generatePostContent($pilar, $format, $excludeTitles = [])
    {
        $seeds = [
            'Arquitectura de Datos' => [
                [
                    'title' => 'La importancia de una base sólida',
                    'copy' => "En el mundo digital, los datos son el petróleo, pero solo si están refinados. Una arquitectura de datos eficiente permite a tu empresa escalar sin caos.\n\nDescubre cómo estructuramos los flujos de información para convertir leads en clientes reales de forma automática.",
                    'cta' => 'Comenta "DATOS" para una auditoría gratuita.',
                    'hashtags' => '#DataArchitecture #CloudComputing #Automation #' . preg_replace('/[^a-zA-Z0-9]/', '', \Core\Config::get('business.company_name')) . ' #BusinessIntelligence',
                    'prompt' => 'A hyper-realistic 3D render of a glowing blue data structure, nexus points connected by golden lines, dark laboratory background, high tech aesthetic.'
                ],
                [
                    'title' => '¿Tu CRM es un cementerio de datos?',
                    'copy' => "Miles de registros pero ninguna venta. Ese es el resultado de una mala arquitectura. En " . \Core\Config::get('business.company_name') . " diseñamos estructuras que trabajan por ti.\n\nMenos ruido, más señales de compra.",
                    'cta' => 'Escríbenos por DM para optimizar tu CRM.',
                    'hashtags' => '#DataCleanup #CRM #Optimization #Efficiency #TechStack',
                    'prompt' => 'A digital warehouse with neatly organized glowing boxes, light trails moving between them. Cyberpunk office style.'
                ],
                [
                    'title' => 'Escalabilidad: El secreto está en los cimientos',
                    'copy' => "No puedes construir un rascacielos sobre arena. Tu arquitectura de datos debe estar lista para el crecimiento masivo de tu empresa.\n\nPreparamos tu negocio para el siguiente nivel de volumen.",
                    'cta' => 'Lee el artículo completo en nuestro blog.',
                    'hashtags' => '#Scalability #Architecture #Growth #FutureReady #Engineering',
                    'prompt' => 'Architectural blueprint of a digital city merging with real-world server racks. Double exposure effect.'
                ]
            ],
            'Transformación Inteligente' => [
                [
                    'title' => 'IA: De la expectativa a la realidad',
                    'copy' => "¿Tu equipo pierde tiempo en tareas repetitivas? La Transformación Inteligente no es solo usar ChatGPT, es integrar modelos de IA en tu flujo de trabajo diario.\n\nPasamos de procesos manuales a decisiones basadas en IA en menos de 30 días.",
                    'cta' => 'Más información en el link de la bio.',
                    'hashtags' => '#AI #ArtificialIntelligence #ModernBusiness #Efficiency #DigitalTransformation',
                    'prompt' => 'A sleek, premium office environment where a holographic assistant interacts with a developer. Cinematic lighting, deep indigo and cyan tones.'
                ],
                [
                    'title' => 'Automatización vs Transformación',
                    'copy' => "Automatizar es hacer lo mismo más rápido. Transformación Inteligente es repensar el proceso usando IA para obtener resultados que antes eran imposibles.\n\n¿Estás solo acelerando el caos o transformando tu negocio?",
                    'cta' => 'Agenda una sesión de estrategia hoy.',
                    'hashtags' => '#BusinessStrategy #Innovation #AI #Automation #FutureOfWork',
                    'prompt' => 'Two gears turning, one is traditional iron, the other is made of pure light and neural networks. Dramatic contrast.'
                ],
                [
                    'title' => 'El impacto de la IA en el ROI',
                    'copy' => "Implementar IA no es un gasto, es la inversión más rentable de 2024. Reducción de costos operativos y aumento de conversión en un solo paso.\n\nTe mostramos los números de nuestros casos de éxito.",
                    'cta' => 'Solicita nuestro reporte de resultados.',
                    'hashtags' => '#ROI #BusinessValue #AI #Fintech #SmartSolutions',
                    'prompt' => 'A financial chart showing a sharp upward trend, where the bars are transformed into digital code pulses. Green and gold accents.'
                ]
            ],
            'Soluciones en Producción' => [
                [
                    'title' => 'Caso de Éxito: CRM a Medida',
                    'copy' => "No vendemos software, entregamos soluciones. Este mes logramos reducir el tiempo de respuesta de ventas de un cliente en un 70% gracias a nuestra orquestación comercial.\n\nSoluciones reales para problemas reales.",
                    'cta' => 'Desliza para ver los resultados.',
                    'hashtags' => '#SuccessStory #CRM #WebDevelopment #SoftwareEngineering #TechSolutions',
                    'prompt' => 'A modern smartphone screen displaying a dynamic dashboard with rising graphs. Background is a blurred, stylish coworking space.'
                ],
                [
                    'title' => 'Despliegues sin fricción',
                    'copy' => "La mayoría de las implementaciones de CRM fallan por la adopción. Nuestras Soluciones en Producción incluyen capacitación y flujo intuitivo para que tu equipo las ame desde el día 1.",
                    'cta' => 'Mira nuestra demo en vivo.',
                    'hashtags' => '#SmoothDeployment #UX #TechAdoption #SoftwareSolutions #BusinessFlow',
                    'prompt' => 'A clean workspace with a laptop showing a "Success" notification. Soft natural lighting, minimalist design.'
                ],
                [
                    'title' => 'Orquestación Comercial: El Motor de tus Ventas',
                    'copy' => "Conectamos tu marketing con tus ventas y tu post-venta. Un flujo continuo donde nada se pierde y todo se mide.\n\nTu empresa funcionando como una orquesta perfecta.",
                    'cta' => 'Reserva tu consultoría inicial.',
                    'hashtags' => '#SalesFlow #Orchestration #BusinessGrowth #FullStackSales',
                    'prompt' => 'A conductor\'s baton guiding lines of light that represent different departments. Abstract and powerful.'
                ]
            ],
            'Branding / Comunidad' => [
                [
                    'title' => 'Detrás de escena en VeZetaeLeA',
                    'copy' => "Somos un ecosistema de innovación. Hoy te mostramos cómo nuestro equipo de ingeniería trabaja para asegurar que el CRM esté siempre online y optimizado.\n\nLa tecnología es humana.",
                    'cta' => '¿Qué te gustaría saber de nosotros?',
                    'hashtags' => '#TeamWork #TechCulture #BehindTheScenes #Innovation #DevLife',
                    'prompt' => 'A wide shot of a creative tech team brainstorming around a large digital white-board. Warm lighting, vibrant atmosphere.'
                ],
                [
                    'title' => 'Nuestros valores: Transparencia y Rapidez',
                    'copy' => "En el mundo tech, la velocidad lo es todo, pero la confianza es más importante. En VeZetaeLeA priorizamos la comunicación clara con nuestros clientes.\n\nSomos tus socios tecnológicos.",
                    'cta' => 'Conoce más sobre nuestra cultura.',
                    'hashtags' => '#CompanyCulture #TechValues #Transparency #Partnership',
                    'prompt' => 'Two hands shaking, but the contact point emits a pulse of blue energy. Professional yet futuristic.'
                ],
                [
                    'title' => 'La visión de VeZetaeLeA para 2026',
                    'copy' => "No estamos viendo lo que pasa hoy, estamos construyendo lo que será estándar mañana. Únete a la comunidad de empresas que lideran la transformación digital.",
                    'cta' => 'Sé parte del cambio.',
                    'hashtags' => '#Visionary #FutureTech #BusinessLeadership #InnovationHub',
                    'prompt' => 'A person looking out of a large glass window at a futuristic city at sunset. Deep orange and violet colors.'
                ]
            ]
        ];

        $options = $seeds[$pilar] ?? $seeds['Branding / Comunidad'];

        $availableOptions = array_filter($options, function ($opt) use ($excludeTitles) {
            return !in_array($opt['title'], $excludeTitles);
        });

        if (empty($availableOptions)) {
            $availableOptions = $options;
        }

        return $availableOptions[array_rand($availableOptions)];
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

        $newContent = $this->generatePostContent($oldPost['strategic_pilar'], $oldPost['post_format'], [$oldPost['internal_title']]);

        $sql = "UPDATE instagram_posts SET 
                internal_title = :title, 
                copy_text = :copy, 
                cta_text = :cta, 
                hashtags = :hashtags, 
                visual_prompt = :prompt
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $newContent['title'],
            ':copy' => $newContent['copy'],
            ':cta' => $newContent['cta'],
            ':hashtags' => $newContent['hashtags'],
            ':prompt' => $newContent['prompt'],
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

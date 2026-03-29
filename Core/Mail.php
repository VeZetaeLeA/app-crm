<?php
namespace Core;

class Mail
{
    /**
     * Send an email by pushing it to the asynchronous Queue
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email content (HTML)
     * @return bool
     */
    public static function send($to, $subject, $body)
    {
        if (!\Core\Config::get('mail_enabled', false)) {
            return true; // Mail disabled globally, silently skip
        }

        $useQueue = getenv('MAIL_QUEUE') !== 'false';

        if ($useQueue) {
            // Async mode: push to DB queue (needs worker.php running)
            \Core\SecurityLogger::log('email_queued', ['to' => $to, 'subject' => $subject], 'INFO');
            try {
                \Core\Queue::push('App\Jobs\SendEmailJob', [
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $body
                ]);
                return true;
            } catch (\Exception $e) {
                \Core\SecurityLogger::log('email_queue_failed', [
                    'to' => $to,
                    'subject' => $subject,
                    'error' => $e->getMessage()
                ], 'ERROR');
                return false;
            }
        }

        // Sync mode: send directly via PHPMailer (no worker needed — best for shared hosting)
        $mailConfig = \Core\Config::get('mail');
        if (empty($mailConfig['host'])) {
            \Core\SecurityLogger::log('email_failed', ['to' => $to, 'error' => 'MAIL_HOST not set'], 'ERROR');
            return false;
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $mailConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mailConfig['user'];
            $mail->Password = $mailConfig['pass'];
            $mail->SMTPSecure = strtolower($mailConfig['enc']) === 'tls'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $mailConfig['port'] ?: 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($mailConfig['from_address'], $mailConfig['from_name'] ?: 'Vezetaelea');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();

            \Core\SecurityLogger::log('email_sent', ['to' => $to, 'subject' => $subject], 'INFO');
            return true;
        } catch (\Exception $e) {
            \Core\SecurityLogger::log('email_failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $mail->ErrorInfo
            ], 'ERROR');
            return false;
        }
    }

    /**
     * Log email content
     */
    private static function log($to, $subject, $body)
    {
        $logPath = BASE_PATH . '/storage/logs/mail.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] TO: $to | SUBJECT: $subject\nBODY:\n$body\n" . str_repeat('-', 50) . "\n";

        @file_put_contents($logPath, $logEntry, FILE_APPEND);
    }

    /**
     * Send Welcome Email
     */
    public static function sendWelcome($to, $name, $password)
    {
        $appUrl = rtrim(Config::get('base_url'), '/');
        $companyName = Config::get('business.company_name');
        $subject = "¡Bienvenido a $companyName, $name!";
        $body = "
            <div style='font-family: Arial; background: #0A0A0A; color: white; padding: 40px; max-width: 600px; margin: auto; border: 1px solid #333;'>
                <h1 style='text-align: center; margin: 0; background: linear-gradient(to right, #D4AF37, #30C5FF); -webkit-background-clip: text; color: transparent;'>$companyName</h1>
                <p>Hola <strong>$name</strong>,</p>
                <p>Tu cuenta ha sido creada exitosamente para procesar tu solicitud de servicio.</p>
                
                <div style='background: #111; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #D4AF37;'>
                    <p style='margin-top: 0;'><strong>Tus credenciales de acceso:</strong></p>
                    <p>Usuario: <span style='color: #30C5FF;'>$to</span></p>
                    <p>Contraseña Temporal: <span style='color: #D4AF37;'>$password</span></p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/profile/settings#change-password' style='background: #D4AF37; color: black; padding: 14px 28px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Cambiar Contraseña</a>
                </div>

                <p style='color: #888; font-size: 13px;'>Nota: Por seguridad, te recomendamos cambiar tu contraseña una vez que inicies sesión.</p>
                <hr style='border: 0; border-top: 1px solid #333; margin: 30px 0;'>
                <p style='text-align: center; color: #666;'>Equipo de Ingeniería - $companyName</p>
            </div>
        ";
        return self::send($to, $subject, $body);
    }

    /**
     * Send Ticket Status Update
     */
    public static function sendTicketUpdate($to, $ticketNumber, $status)
    {
        $appUrl = rtrim(Config::get('base_url'), '/');
        $subject = "Actualización de Ticket: $ticketNumber";
        $body = "
            <div style='font-family: Arial; background: #0A0A0A; color: white; padding: 40px;'>
                <h2 style='margin: 0 0 20px 0; background: linear-gradient(to right, #D4AF37, #30C5FF); -webkit-background-clip: text; color: transparent;'>Actualización de Estado</h2>
                <p>Tu ticket <strong>$ticketNumber</strong> ha cambiado su estado a: <span style='color: #30C5FF;'>$status</span>.</p>
                <p>Revisa los detalles en la plataforma.</p>
                <a href='{$appUrl}/dashboard' style='background: #D4AF37; color: black; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Ver Ticket</a>
            </div>
        ";
        return self::send($to, $subject, $body);
    }

    /**
     * Send Request Confirmation (PRD v1.0)
     */
    public static function sendRequestConfirmation($to, $name, $ticketNumber, $subject_text)
    {
        $appUrl = rtrim(Config::get('base_url'), '/');
        $companyName = Config::get('business.company_name');
        $slogan = Config::get('business.company_slogan');
        $subject = "Confirmación de Solicitud: $ticketNumber";
        $body = "
            <div style='font-family: Arial, sans-serif; background: #0A0A0A; color: white; padding: 40px; max-width: 600px; margin: auto; border: 1px solid #333;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='margin: 0; background: linear-gradient(to right, #D4AF37, #30C5FF); -webkit-background-clip: text; color: transparent;'>$companyName</h1>
                    <p style='color: #888; font-size: 14px; text-transform: uppercase; letter-spacing: 2px;'>$slogan</p>
                </div>
                
                <h2 style='color: #30C5FF; text-align: center;'>¡Solicitud Recibida!</h2>
                <p>Hola <strong>$name</strong>,</p>
                <p>Hemos recibido correctamente tu solicitud: <strong>\"$subject_text\"</strong>.</p>
                
                <div style='background: #111; padding: 25px; border-radius: 12px; border: 1px solid #333; margin: 30px 0;'>
                    <h4 style='color: #D4AF37; margin-top: 0;'>¿Qué sigue ahora?</h4>
                    <ol style='padding-left: 20px; color: #ccc; font-size: 14px; line-height: 1.6;'>
                        <li style='margin-bottom: 10px;'><strong>Revisión Técnica:</strong> Un especialista analizará tu requerimiento.</li>
                        <li style='margin-bottom: 10px;'><strong>Propuesta Comercial:</strong> Recibirás un presupuesto en tu dashboard.</li>
                        <li style='margin-bottom: 10px;'><strong>Activación:</strong> Una vez aprobado, iniciaremos la ejecución iterativa.</li>
                    </ol>
                </div>

                <div style='text-align: center; margin: 40px 0;'>
                    <a href='{$appUrl}/dashboard' style='background: #D4AF37; color: black; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Acceder a mi Dashboard</a>
                </div>

                <p style='color: #666; font-size: 12px; text-align: center; border-top: 1px solid #222; padding-top: 30px;'>
                    Este es un correo automático, por favor no respondas directamente. Si tienes dudas, contáctanos a través del chat de la plataforma.
                </p>
            </div>
        ";
        return self::send($to, $subject, $body);
    }

    /**
     * Send Budget Available
     */
    public static function sendBudgetAvailable($to, $name, $budgetNumber, $budgetId)
    {
        $appUrl = rtrim(Config::get('base_url'), '/');
        $companyName = Config::get('business.company_name');
        $slogan = Config::get('business.company_slogan');
        $subject = "Presupuesto Disponible: $budgetNumber";
        $body = "
            <div style='font-family: Arial, sans-serif; background: #0A0A0A; color: white; padding: 40px; max-width: 600px; margin: auto; border: 1px solid #333;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='margin: 0; background: linear-gradient(to right, #D4AF37, #30C5FF); -webkit-background-clip: text; color: transparent;'>$companyName</h1>
                    <p style='color: #888; font-size: 14px; text-transform: uppercase; letter-spacing: 2px;'>$slogan</p>
                </div>
                
                <h2 style='color: #30C5FF; text-align: center;'>Propuesta Comercial Lista</h2>
                <p>Hola <strong>$name</strong>,</p>
                <p>Hemos generado el presupuesto <strong>$budgetNumber</strong> para tu solicitud.</p>
                
                <div style='background: #111; padding: 25px; border-radius: 12px; border: 1px solid #333; margin: 30px 0; text-align: center;'>
                    <p style='color: #ccc; font-size: 15px; margin-bottom: 20px;'>Puedes revisar los detalles de inversión, alcances técnicos y aprobar la propuesta directamente en nuestra plataforma.</p>
                    <a href='{$appUrl}/budget/show/{$budgetId}' style='background: #D4AF37; color: black; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Ver Propuesta Comercial</a>
                </div>

                <p style='color: #666; font-size: 12px; text-align: center; border-top: 1px solid #222; padding-top: 30px;'>
                    Este es un correo automático. Si tienes alguna observación sobre el presupuesto, usa el panel de discusión en el detalle de la propuesta.
                </p>
            </div>
        ";
        return self::send($to, $subject, $body);
    }

    /**
     * Send Urgent Support Notification
     */
    public static function sendUrgentSupport($to, $clientName, $clientEmail, $ticketId)
    {
        $appUrl = rtrim(Config::get('base_url'), '/');
        $companyName = Config::get('business.company_name');
        $subject = "[URGENTE] Soporte Prioritario: $clientName";
        $body = "
            <div style='font-family: Arial, sans-serif; background: #0A0A0A; color: white; padding: 40px; max-width: 600px; margin: auto; border: 1px solid #D4AF37;'>
                <h2 style='color: #FF5555; text-align: center;'>⚠️ ATENCIÓN INMEDIATA SOLICITADA</h2>
                <p>El cliente <strong>$clientName</strong> ($clientEmail) ha solicitado soporte prioritario.</p>
                
                <div style='background: #111; padding: 20px; border-radius: 8px; border: 1px solid #333; margin: 20px 0;'>
                    <p><strong>Ticket Relacionado:</strong> #$ticketId</p>
                    <p><strong>Estado:</strong> Urgente</p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/ticket/detail/$ticketId' style='background: #30C5FF; color: black; padding: 14px 28px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Atender Requerimiento</a>
                </div>
            </div>
        ";
        return self::send($to, $subject, $body);
    }
    /**
     * SPRINT 2.1 — Notificación al cliente cuando se sube un entregable
     */
    public static function sendDeliverableReady($to, $clientName, $serviceName, $deliverableTitle, $deliverableDesc, $deliverableId)
    {
        $appUrl = rtrim(Config::get('base_url'), '/');
        $companyName = Config::get('business.company_name');
        $slogan = Config::get('business.company_slogan');
        $subject = "\u{1F4E6} Nuevo Entregable Disponible: $deliverableTitle";
        $body = "
            <div style='font-family: Arial, sans-serif; background: #0A0A0A; color: white; padding: 40px; max-width: 600px; margin: auto; border: 1px solid #333;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='margin: 0; background: linear-gradient(to right, #D4AF37, #30C5FF); -webkit-background-clip: text; color: transparent;'>$companyName</h1>
                    <p style='color: #888; font-size: 14px; text-transform: uppercase; letter-spacing: 2px;'>$slogan</p>
                </div>

                <div style='background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(48,197,255,0.1)); border: 1px solid rgba(212,175,55,0.3); border-radius: 12px; padding: 20px; margin-bottom: 25px; text-align: center;'>
                    <span style='font-size: 48px;'>&#128230;</span>
                    <h2 style='color: #D4AF37; margin: 10px 0 5px 0;'>Nuevo Entregable Listo</h2>
                    <p style='color: #ccc; margin: 0;'>Tu equipo ha preparado un nuevo entregable para ti</p>
                </div>

                <p>Hola <strong>$clientName</strong>,</p>
                <p>Te informamos que el equipo ha subido un nuevo entregable en tu proyecto <strong>$serviceName</strong>.</p>

                <div style='background: #111; padding: 20px; border-radius: 12px; border-left: 4px solid #D4AF37; margin: 25px 0;'>
                    <p style='color: #D4AF37; font-weight: bold; margin: 0 0 8px 0; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;'>Detalle del Entregable</p>
                    <h3 style='color: white; margin: 0 0 8px 0;'>$deliverableTitle</h3>
                    <p style='color: #ccc; font-size: 14px; margin: 0;'>$deliverableDesc</p>
                </div>

                <div style='background: #111; padding: 20px; border-radius: 12px; border: 1px solid #333; margin: 25px 0;'>
                    <h4 style='color: #30C5FF; margin: 0 0 15px 0;'>\u{2139}\uFE0F Accion requerida</h4>
                    <p style='color: #ccc; font-size: 14px; margin: 0 0 10px 0;'>Puedes revisar el archivo y aprobarlo o rechazarlo directamente desde tu workspace de proyecto.</p>
                    <ul style='color: #999; font-size: 13px; padding-left: 20px;'>
                        <li style='margin-bottom: 6px;'><strong style='color: #4CAF50;'>Aprobar:</strong> Confirma que el entregable cumple lo acordado.</li>
                        <li style='margin-bottom: 6px;'><strong style='color: #FF5555;'>Rechazar:</strong> Indica las correcciones necesarias con tus notas.</li>
                    </ul>
                </div>

                <div style='text-align: center; margin: 40px 0;'>
                    <a href='{$appUrl}/project/workspace' style='background: linear-gradient(135deg, #D4AF37, #B8962E); color: black; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 16px;'>\u{1F4C2} Ver mi Workspace</a>
                </div>

                <p style='color: #666; font-size: 12px; text-align: center; border-top: 1px solid #222; padding-top: 30px;'>
                    Este es un correo automatico generado por la plataforma $companyName.
                </p>
            </div>
        ";
        return self::send($to, $subject, $body);
    }
}

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
        if (!\Core\Config::get('mail.enabled', false)) {
            return true; // Mail disabled globally, silently skip
        }

        $useQueue = getenv('MAIL_QUEUE') === 'true';

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
        $subject = \Core\Lang::get('mail.welcome.subject', ['company' => $companyName, 'name' => $name]);
        $body = "
            <div style='font-family: Arial; background: #0A0A0A; color: white; padding: 40px; max-width: 600px; margin: auto; border: 1px solid #333;'>
                <h1 style='text-align: center; margin: 0; background: linear-gradient(to right, #D4AF37, #30C5FF); -webkit-background-clip: text; color: transparent;'>$companyName</h1>
                <p>" . \Core\Lang::get('mail.welcome.greeting', ['name' => "<strong>$name</strong>"]) . "</p>
                <p>" . \Core\Lang::get('mail.welcome.intro') . "</p>
                
                <div style='background: #111; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #D4AF37;'>
                    <p style='margin-top: 0;'><strong>" . \Core\Lang::get('mail.welcome.credentials_title') . "</strong></p>
                    <p>" . \Core\Lang::get('mail.welcome.user_label') . " <span style='color: #30C5FF;'>$to</span></p>
                    <p>" . \Core\Lang::get('mail.welcome.temp_pass_label') . " <span style='color: #D4AF37;'>$password</span></p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/profile/settings#change-password' style='background: #D4AF37; color: black; padding: 14px 28px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>" . \Core\Lang::get('mail.welcome.btn_change_pass') . "</a>
                </div>

                <p style='color: #888; font-size: 13px;'>" . \Core\Lang::get('mail.welcome.security_note') . "</p>
                <hr style='border: 0; border-top: 1px solid #333; margin: 30px 0;'>
                <p style='text-align: center; color: #666;'>" . \Core\Lang::get('mail.welcome.team_signature', ['company' => $companyName]) . "</p>
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
        $subject = \Core\Lang::get('mail.ticket_update.subject', ['ticketNumber' => $ticketNumber]);
        $body = "
            <div style='font-family: Arial; background: #0A0A0A; color: white; padding: 40px;'>
                <h2 style='margin: 0 0 20px 0; background: linear-gradient(to right, #D4AF37, #30C5FF); -webkit-background-clip: text; color: transparent;'>" . \Core\Lang::get('mail.ticket_update.title') . "</h2>
                <p>" . \Core\Lang::get('mail.ticket_update.status_msg', ['ticketNumber' => "<strong>$ticketNumber</strong>", 'status' => "<span style='color: #30C5FF;'>$status</span>"]) . "</p>
                <p>" . \Core\Lang::get('mail.ticket_update.check_details') . "</p>
                <a href='{$appUrl}/dashboard' style='background: #D4AF37; color: black; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>" . \Core\Lang::get('mail.ticket_update.btn_view_ticket') . "</a>
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
        $subject = \Core\Lang::get('mail.request_confirmation.subject', ['ticketNumber' => $ticketNumber]);
        $body = "
            <div style='font-family: Arial, sans-serif; background: #0A0A0A; color: white; padding: 40px; max-width: 600px; margin: auto; border: 1px solid #333;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='margin: 0; background: linear-gradient(to right, #D4AF37, #30C5FF); -webkit-background-clip: text; color: transparent;'>$companyName</h1>
                    <p style='color: #888; font-size: 14px; text-transform: uppercase; letter-spacing: 2px;'>$slogan</p>
                </div>
                
                <h2 style='color: #30C5FF; text-align: center;'>" . \Core\Lang::get('mail.request_confirmation.title') . "</h2>
                <p>" . \Core\Lang::get('mail.request_confirmation.greeting', ['name' => "<strong>$name</strong>"]) . "</p>
                <p>" . \Core\Lang::get('mail.request_confirmation.received', ['subject_text' => "<strong>$subject_text</strong>"]) . "</p>
                
                <div style='background: #111; padding: 25px; border-radius: 12px; border: 1px solid #333; margin: 30px 0;'>
                    <h4 style='color: #D4AF37; margin-top: 0;'>" . \Core\Lang::get('mail.request_confirmation.whats_next') . "</h4>
                    <ol style='padding-left: 20px; color: #ccc; font-size: 14px; line-height: 1.6;'>
                        <li style='margin-bottom: 10px;'><strong>" . \Core\Lang::get('mail.request_confirmation.step_1_title') . "</strong> " . \Core\Lang::get('mail.request_confirmation.step_1_desc') . "</li>
                        <li style='margin-bottom: 10px;'><strong>" . \Core\Lang::get('mail.request_confirmation.step_2_title') . "</strong> " . \Core\Lang::get('mail.request_confirmation.step_2_desc') . "</li>
                        <li style='margin-bottom: 10px;'><strong>" . \Core\Lang::get('mail.request_confirmation.step_3_title') . "</strong> " . \Core\Lang::get('mail.request_confirmation.step_3_desc') . "</li>
                    </ol>
                </div>

                <div style='text-align: center; margin: 40px 0;'>
                    <a href='{$appUrl}/dashboard' style='background: #D4AF37; color: black; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>" . \Core\Lang::get('mail.request_confirmation.btn_dashboard') . "</a>
                </div>

                <p style='color: #666; font-size: 12px; text-align: center; border-top: 1px solid #222; padding-top: 30px;'>
                    " . \Core\Lang::get('mail.request_confirmation.automated_msg') . "
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
        $subject = \Core\Lang::get('mail.budget_available.subject', ['budgetNumber' => $budgetNumber]);
        $body = "
            <div style='font-family: Arial, sans-serif; background: #0A0A0A; color: white; padding: 40px; max-width: 600px; margin: auto; border: 1px solid #333;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='margin: 0; background: linear-gradient(to right, #D4AF37, #30C5FF); -webkit-background-clip: text; color: transparent;'>$companyName</h1>
                    <p style='color: #888; font-size: 14px; text-transform: uppercase; letter-spacing: 2px;'>$slogan</p>
                </div>
                
                <h2 style='color: #30C5FF; text-align: center;'>" . \Core\Lang::get('mail.budget_available.title') . "</h2>
                <p>" . \Core\Lang::get('mail.budget_available.greeting', ['name' => "<strong>$name</strong>"]) . "</p>
                <p>" . \Core\Lang::get('mail.budget_available.generated_msg', ['budgetNumber' => "<strong>$budgetNumber</strong>"]) . "</p>
                
                <div style='background: #111; padding: 25px; border-radius: 12px; border: 1px solid #333; margin: 30px 0; text-align: center;'>
                    <p style='color: #ccc; font-size: 15px; margin-bottom: 20px;'>" . \Core\Lang::get('mail.budget_available.details_msg') . "</p>
                    <a href='{$appUrl}/budget/show/{$budgetId}' style='background: #D4AF37; color: black; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>" . \Core\Lang::get('mail.budget_available.btn_view_proposal') . "</a>
                </div>

                <p style='color: #666; font-size: 12px; text-align: center; border-top: 1px solid #222; padding-top: 30px;'>
                    " . \Core\Lang::get('mail.budget_available.automated_msg') . "
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
        $subject = \Core\Lang::get('mail.urgent_support.subject', ['clientName' => $clientName]);
        $body = "
            <div style='font-family: Arial, sans-serif; background: #0A0A0A; color: white; padding: 40px; max-width: 600px; margin: auto; border: 1px solid #D4AF37;'>
                <h2 style='color: #FF5555; text-align: center;'>" . \Core\Lang::get('mail.urgent_support.title') . "</h2>
                <p>" . \Core\Lang::get('mail.urgent_support.requested_msg', ['clientName' => "<strong>$clientName</strong>", 'clientEmail' => $clientEmail]) . "</p>
                
                <div style='background: #111; padding: 20px; border-radius: 8px; border: 1px solid #333; margin: 20px 0;'>
                    <p><strong>" . \Core\Lang::get('mail.urgent_support.related_ticket') . "</strong> #$ticketId</p>
                    <p><strong>" . \Core\Lang::get('mail.urgent_support.status_label') . "</strong> " . \Core\Lang::get('mail.urgent_support.status_urgent') . "</p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/ticket/detail/$ticketId' style='background: #30C5FF; color: black; padding: 14px 28px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>" . \Core\Lang::get('mail.urgent_support.btn_attend') . "</a>
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
        $subject = \Core\Lang::get('mail.deliverable_ready.subject', ['deliverableTitle' => $deliverableTitle]);
        $body = "
            <div style='font-family: Arial, sans-serif; background: #0A0A0A; color: white; padding: 40px; max-width: 600px; margin: auto; border: 1px solid #333;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='margin: 0; background: linear-gradient(to right, #D4AF37, #30C5FF); -webkit-background-clip: text; color: transparent;'>$companyName</h1>
                    <p style='color: #888; font-size: 14px; text-transform: uppercase; letter-spacing: 2px;'>$slogan</p>
                </div>

                <div style='background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(48,197,255,0.1)); border: 1px solid rgba(212,175,55,0.3); border-radius: 12px; padding: 20px; margin-bottom: 25px; text-align: center;'>
                    <span style='font-size: 48px;'>&#128230;</span>
                    <h2 style='color: #D4AF37; margin: 10px 0 5px 0;'>" . \Core\Lang::get('mail.deliverable_ready.title') . "</h2>
                    <p style='color: #ccc; margin: 0;'>" . \Core\Lang::get('mail.deliverable_ready.subtitle') . "</p>
                </div>

                <p>" . \Core\Lang::get('mail.deliverable_ready.greeting', ['clientName' => "<strong>$clientName</strong>"]) . "</p>
                <p>" . \Core\Lang::get('mail.deliverable_ready.uploaded_msg', ['serviceName' => "<strong>$serviceName</strong>"]) . "</p>

                <div style='background: #111; padding: 20px; border-radius: 12px; border-left: 4px solid #D4AF37; margin: 25px 0;'>
                    <p style='color: #D4AF37; font-weight: bold; margin: 0 0 8px 0; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;'>" . \Core\Lang::get('mail.deliverable_ready.detail_title') . "</p>
                    <h3 style='color: white; margin: 0 0 8px 0;'>$deliverableTitle</h3>
                    <p style='color: #ccc; font-size: 14px; margin: 0;'>$deliverableDesc</p>
                </div>

                <div style='background: #111; padding: 20px; border-radius: 12px; border: 1px solid #333; margin: 25px 0;'>
                    <h4 style='color: #30C5FF; margin: 0 0 15px 0;'>" . \Core\Lang::get('mail.deliverable_ready.action_required') . "</h4>
                    <p style='color: #ccc; font-size: 14px; margin: 0 0 10px 0;'>" . \Core\Lang::get('mail.deliverable_ready.action_desc') . "</p>
                    <ul style='color: #999; font-size: 13px; padding-left: 20px;'>
                        <li style='margin-bottom: 6px;'><strong style='color: #4CAF50;'>" . \Core\Lang::get('mail.deliverable_ready.approve_label') . "</strong> " . \Core\Lang::get('mail.deliverable_ready.approve_desc') . "</li>
                        <li style='margin-bottom: 6px;'><strong style='color: #FF5555;'>" . \Core\Lang::get('mail.deliverable_ready.reject_label') . "</strong> " . \Core\Lang::get('mail.deliverable_ready.reject_desc') . "</li>
                    </ul>
                </div>

                <div style='text-align: center; margin: 40px 0;'>
                    <a href='{$appUrl}/project/workspace' style='background: linear-gradient(135deg, #D4AF37, #B8962E); color: black; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 16px;'>" . \Core\Lang::get('mail.deliverable_ready.btn_workspace') . "</a>
                </div>

                <p style='color: #666; font-size: 12px; text-align: center; border-top: 1px solid #222; padding-top: 30px;'>
                    " . \Core\Lang::get('mail.deliverable_ready.automated_msg', ['company' => $companyName]) . "
                </p>
            </div>
        ";
        return self::send($to, $subject, $body);
    }
}

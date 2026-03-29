<?php
namespace App\Controllers;

use Core\Controller;
use Core\SecurityLogger;
use App\Services\InvoiceService;

/**
 * WebhookController - Entry point for external notifications (mercadopago, stripe, etc.)
 */
class WebhookController extends Controller
{

    /**
     * Handle MercadoPago payment notifications
     */
    public function mercadopago()
    {
        // MercadoPago sends JSON data for events
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            http_response_code(400);
            return;
        }

        // 1. Log the incoming notification for audit trail
        SecurityLogger::log('webhook_mercadopago_received', [
            'type' => $data['type'] ?? 'unknown',
            'action' => $data['action'] ?? 'unknown',
            'resource_id' => $data['data']['id'] ?? 'unknown'
        ]);

        // 2. Process only payment events
        if (isset($data['type']) && $data['type'] === 'payment') {
            $paymentId = $data['data']['id'] ?? null;
            if ($paymentId) {
                // Call MercadoPago API to verify payment status
                $mpToken = \Core\Config::get('payment.mp_access_token');

                $ch = curl_init("https://api.mercadopago.com/v1/payments/{$paymentId}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $mpToken
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $paymentInfo = json_decode($response, true);

                    if (isset($paymentInfo['status']) && $paymentInfo['status'] === 'approved') {
                        $invoiceId = $paymentInfo['external_reference'] ?? null;
                        $transactionAmount = $paymentInfo['transaction_amount'] ?? 0;
                        
                        // 🚀 Multi-Currency Fix: Check metadata for original amount
                        $metadata = $paymentInfo['metadata'] ?? [];
                        if (!empty($metadata['original_amount'])) {
                            $amount = (float) $metadata['original_amount'];
                        } else {
                            // Fallback: Inverse conversion using current rate
                            $exchangeRate = (float) \Core\Config::get('payment.exchange_rate') ?: 1;
                            $amount = $transactionAmount / $exchangeRate;
                        }

                        if ($invoiceId) {
                            $db = \Core\Database::getInstance()->getConnection();

                            // 1. Insert automatic receipt
                            $sql = "INSERT INTO payment_receipts (invoice_id, uploaded_by, filename, filepath, amount, payment_date, status) 
                                    VALUES (?, 1, 'mercadopago_auto', 'webhook', ?, CURDATE(), 'pending')";
                            $stmt = $db->prepare($sql);
                            $stmt->execute([$invoiceId, $amount]);

                            // 2. Confirm payment
                            $invoiceService = new InvoiceService();
                            // ID 1 (System Admin) acts as the verifier
                            $result = $invoiceService->confirmPayment((int) $invoiceId, 1);

                            if (!$result['success']) {
                                SecurityLogger::log('mp_webhook_confirm_failed', ['invoice_id' => $invoiceId, 'error' => $result['error']]);
                            }
                        }
                    }
                }
            }

            SecurityLogger::log('webhook_payment_processing', ['id' => $paymentId]);
        }

        // Response 200/201 is required by MP to acknowledge receipt
        http_response_code(200);
        echo json_encode(['status' => 'received']);
    }

    /**
     * Test / Debug endpoint
     */
    public function test()
    {
        echo "Webhook endpoint active and CSRF-exempt.";
    }
}

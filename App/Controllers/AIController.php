<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Services\AIService;

class AIController extends Controller
{
    private $aiService;
    private $db;

    public function __construct()
    {
        $this->aiService = new AIService();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * GAI-01: Genera el resumen de un ticket para el Staff.
     */
    public function generateSummary($ticketId)
    {
        if (!Auth::isAdmin() && !Auth::isStaff()) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        // Obtener historial del ticket (Correxión: chat_messages)
        $stmt = $this->db->prepare("SELECT m.*, u.role FROM chat_messages m LEFT JOIN users u ON m.user_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at ASC");
        $stmt->execute([$ticketId]);
        $chats = $stmt->fetchAll();

        if (count($chats) < 1) { 
            echo json_encode(['success' => false, 'error' => 'Inicia la conversación para que Copilot pueda generar un resumen.']);
            return;
        }

        $messagesForAI = array_map(function($chat) {
            $role = $chat['role'] ?? 'system';
            return [
                'is_admin' => in_array($role, ['admin', 'staff']),
                'created_at' => $chat['created_at'],
                'message' => $chat['message']
            ];
        }, $chats);

        $summary = $this->aiService->generateTicketSummary($messagesForAI);

        if (!$summary) {
            echo json_encode(['success' => false, 'error' => 'No se pudo generar el resumen. Verifica tu API Key o conexión.']);
            return;
        }

        echo json_encode(['success' => true, 'summary' => $summary]);
    }

    /**
     * GAI-05: Sugerencia de Respuesta.
     */
    public function suggestResponse($ticketId)
    {
        if (!Auth::isAdmin() && !Auth::isStaff()) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        // Obtener últimos mensajes para contexto
        $stmt = $this->db->prepare("SELECT m.*, u.role FROM chat_messages m LEFT JOIN users u ON m.user_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at DESC LIMIT 10");
        $stmt->execute([$ticketId]);
        $chats = array_reverse($stmt->fetchAll());

        $messagesForAI = array_map(function($chat) {
            $role = $chat['role'] ?? 'system';
            return [
                'is_admin' => in_array($role, ['admin', 'staff']),
                'message' => $chat['message']
            ];
        }, $chats);

        // Obtener descripción original como contexto extra
        $stmtDesc = $this->db->prepare("SELECT description FROM tickets WHERE id = ?");
        $stmtDesc->execute([$ticketId]);
        $description = $stmtDesc->fetchColumn() ?: '';

        $suggestion = $this->aiService->suggestResponse($messagesForAI, $description);

        if (!$suggestion) {
            echo json_encode(['success' => false, 'error' => 'Inicia el diálogo con el cliente para que Copilot pueda sugerir una respuesta basada en contexto.']);
            return;
        }

        echo json_encode(['success' => true, 'suggestion' => $suggestion]);
    }

    /**
     * GAI-03: Asistente Copilot en Chat.
     */
    public function rewriteDraft()
    {
        if (!Auth::isAdmin() && !Auth::isStaff()) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $draft = $input['draft'] ?? '';
        $tone = $input['tone'] ?? 'formal y profesional';

        if (empty($draft)) {
            echo json_encode(['success' => false, 'error' => 'Borrador vacío']);
            return;
        }

        $rewritten = $this->aiService->rewriteDraft($draft, $tone);

        if (!$rewritten) {
            echo json_encode(['success' => false, 'error' => 'El Copilot no pudo reescribir el borrador.']);
            return;
        }

        echo json_encode(['success' => true, 'rewritten' => $rewritten]);
    }
}

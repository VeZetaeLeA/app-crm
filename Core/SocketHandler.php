<?php
namespace Core;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Core\Database;
use PDO;

class SocketHandler implements MessageComponentInterface
{
    protected $clients;
    protected $userConnections; // userId => [connectionIds]

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        
        // Auth via Session Cookie
        $userId = $this->authenticate($conn);
        
        if ($userId) {
            $conn->userId = $userId;
            $this->userConnections[$userId][] = $conn->resourceId;
            echo "New connection! ({$conn->resourceId}) User ID: {$userId}\n";
        } else {
            echo "New anonymous connection! ({$conn->resourceId})\n";
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        if (!$data) return;

        // Handle client-side messages if needed (e.g. ping)
        if (isset($data['type']) && $data['type'] === 'ping') {
            $from->send(json_encode(['type' => 'pong']));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        if (isset($conn->userId)) {
            $userId = $conn->userId;
            if (isset($this->userConnections[$userId])) {
                $this->userConnections[$userId] = array_diff($this->userConnections[$userId], [$conn->resourceId]);
                if (empty($this->userConnections[$userId])) {
                    unset($this->userConnections[$userId]);
                }
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Broadcast to all connected clients
     */
    public function broadcast($msg)
    {
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

    /**
     * Send message to a specific user
     */
    public function sendToUser($userId, $msg)
    {
        if (isset($this->userConnections[$userId])) {
            foreach ($this->clients as $client) {
                if (isset($client->userId) && $client->userId == $userId) {
                    $client->send($msg);
                }
            }
        }
    }

    /**
     * RF-07: Autenticación JWT para WebSockets
     */
    private function authenticate(ConnectionInterface $conn)
    {
        // 1. Extraer token del Query String (standard para WS: ws://host?token=...)
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $query);
        $token = $query['token'] ?? null;

        // 2. Fallback: Cabecera Authorization: Bearer
        if (!$token) {
            $authHeader = $conn->httpRequest->getHeader('Authorization');
            if (!empty($authHeader)) {
                $token = str_replace('Bearer ', '', $authHeader[0]);
            }
        }

        if (!$token) return null;

        try {
            // Necesitamos una instancia de la BD para inicializar JWT
            $db = Database::getInstance()->getConnection();
            $jwt = new \Core\JWT($db);
            
            $payload = $jwt->decode($token);
            
            if (!$payload) {
                echo "Socket Auth Failed: Invalid Token\n";
                return null;
            }

            // Guardamos metadatos en la conexión para aislamiento de canales
            $conn->tenantId = $payload['tenant_id'] ?? 1;
            $conn->userRole = $payload['role'] ?? 'guest';

            return (int)$payload['user_id'];
        } catch (\Exception $e) {
            echo "Socket Auth Exception: " . $e->getMessage() . "\n";
            return null;
        }
    }
}


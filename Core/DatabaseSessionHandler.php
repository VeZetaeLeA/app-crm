<?php
namespace Core;

use SessionHandlerInterface;
use PDO;

class DatabaseSessionHandler implements SessionHandlerInterface
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        $stmt = $this->db->prepare("SELECT payload FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        return $result ? base64_decode($result['payload']) : '';
    }

    public function write($id, $data): bool
    {
        $user_id = Auth::check() ? Auth::user()['id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $payload = base64_encode($data);
        $time = time();

        $sql = "REPLACE INTO sessions (id, payload, last_activity, user_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $payload, $time, $user_id, $ip, $agent]);
    }

    public function destroy($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc($max_lifetime): int|false
    {
        $time = time() - $max_lifetime;
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE last_activity < ?");
        $stmt->execute([$time]);
        return $stmt->rowCount();
    }
}

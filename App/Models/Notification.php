<?php
namespace App\Models;

use Core\Database;

class Notification
{
    /**
     * Create a new notification
     */
    public static function send($user_id, $type, $title, $message, $link = null)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at) 
                VALUES (?, ?, ?, ?, ?, 0, NOW())";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$user_id, $type, $title, $message, $link]);

        if ($res) {
            // 🚀 Real-Time Broadcast (E11-011)
            \App\Services\RealTimeService::sendToUser($user_id, 'notification', [
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link
            ]);
        }

        return $res;
    }

    /**
     * Get unread notifications for a user
     */
    public static function getUnread($user_id, $limit = 5)
    {
        $db = Database::getInstance()->getConnection();
        $limit = (int) $limit;
        $stmt = $db->prepare("SELECT * FROM notifications 
                             WHERE user_id = ? AND is_read = 0 
                             ORDER BY created_at DESC LIMIT $limit");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    /**
     * Mark all as read
     */
    public static function markAllRead($user_id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    }

    /**
     * Mark a specific notification as read
     */
    public static function markRead($id, $user_id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $user_id]);
    }

    /**
     * Get notification by ID
     */
    public static function getById($id, $user_id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch();
    }

    /**
     * Count unread
     */
    public static function countUnread($user_id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }
}

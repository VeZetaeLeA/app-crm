<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function __construct()
    {
        if (!Auth::check()) {
            header('HTTP/1.1 401 Unauthorized');
            exit;
        }
    }

    /**
     * Get recent unread notifications
     */
    public function getRecent()
    {
        $user_id = Auth::user()['id'];
        $notifications = Notification::getUnread($user_id);
        $count = Notification::countUnread($user_id);

        $this->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => $count
        ]);
    }

    /**
     * Mark all as read
     */
    public function markRead()
    {
        $user_id = \Core\Auth::user()['id'];
        \App\Models\Notification::markAllRead($user_id);
        $this->json(['success' => true]);
    }

    /**
     * Mark a specific notification as read and redirect
     */
    public function read($id)
    {
        $user_id = Auth::user()['id'];
        $notification = Notification::getById($id, $user_id);

        if ($notification) {
            Notification::markRead($id, $user_id);
            $link = $notification['link'];
            if ($link) {
                // Strip absolute URL if it is from the same platform in another environment
                // For instance, turning https://vezetaelea.com/demo/Vezetaelea/ticket/detail/5 to /ticket/detail/5
                // so the redirect helper can append the proper environment URL base.
                $link = preg_replace('/^https?:\/\/[^\/]+.*?(\/(ticket|invoice|budget|project|dashboard).*)$/i', '$1', $link);
                $this->redirect($link);
            }
        }

        $this->redirect('/dashboard');
    }
}

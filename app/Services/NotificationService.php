<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Notification;
use App\Models\Subscription;
use App\Notifications\SubscriptionReminderNotification;

class NotificationService
{
    public function get($user, array $data)
    {
        try {
            return $user->notifications()
                ->orderBy('created_at', 'desc')
                ->paginate($data['limit'] ?? 10);
        } catch (\Throwable $e) {
            \Log::error("Failed to get notifications: " . $e->getMessage());
            throw new \Exception("Failed to get notifications. " . $e->getMessage());
        }
    }

    public function getAdminLogNotifications($limit = 5)
    {

        try {
            return Notification::query()
                ->with(['notifiable' => function ($query) {
                    $query->select('id', 'full_name', 'email', 'avatar');
                }])
                ->where('sender_id', 1)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate($limit);
        } catch (\Throwable $e) {
            \Log::error("Failed to get notifications: " . $e->getMessage());
            throw new \Exception("Failed to get notifications. " . $e->getMessage());
        }
    }

    public function getById($user, $id)
    {
        try {
            return $user->notifications()->findOrFail($id);
        } catch (\Throwable $e) {
            \Log::error("Failed to get notification {$id}: " . $e->getMessage());
            throw new \Exception("Failed to get notification {$id}. " . $e->getMessage());
        }
    }

    public function markAsRead($user, $id)
    {
        try {
            $notification = $this->getById($user, $id);
            $notification->markAsRead();
            return $notification;
        } catch (\Throwable $e) {
            \Log::error("Failed to mark notification {$id} as read: " . $e->getMessage());
            throw new \Exception("Failed to mark notification {$id} as read. " . $e->getMessage());
        }

    }

    public function markAllAsRead($user)
    {
        try {
            $user->unreadNotifications->markAsRead();
            return ["message" => "All notifications have been marked as read"];
        } catch (\Throwable $e) {
            \Log::error("Failed to mark all notifications as read: " . $e->getMessage());
            throw new \Exception("Failed to mark all notifications as read . " . $e->getMessage());
        }
    }

    public function delete($user, $id)
    {
        try {
            $notification = $this->getById($user, $id);
            $notification->delete();
            return ["message" => "Notification has been deleted"];
        } catch (\Throwable $e) {
            \Log::error("Failed to delete notification {$id}: " . $e->getMessage());
            throw new \Exception("Failed to delete notification {$id}. " . $e->getMessage());
        }
    }

    public function reminderSubscription(array $data)
    {
        try {
            $subscription = Subscription::with('account')->findOrFail($data['subscription_id']);
            $subscription->account->notify(new SubscriptionReminderNotification($subscription, SubscriptionStatus::from($data['status'])));
            return ["message" => "Notification has been sent"];
        } catch (\Throwable $e) {
            \Log::error("Failed to send notification to {$subscription->account_id}: " . $e->getMessage());
            throw new \Exception("Failed to send notification to {$subscription->account_id}. " . $e->getMessage());
        }
    }
}

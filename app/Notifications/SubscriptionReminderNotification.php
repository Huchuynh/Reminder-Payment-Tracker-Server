<?php

namespace App\Notifications;

use App\Enums\AlertChannels;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class SubscriptionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Subscription $subscription;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $map = [
            AlertChannels::EMAIL->value => 'mail',
            AlertChannels::NOTIFICATION->value => ['database', FcmChannel::class],
        ];

        $selected = array_intersect_key($map, array_flip($this->subscription->reminder_channels ?? []));

        \Log::info(collect($selected)->flatten()->values()->all());
        return collect($selected)->flatten()->values()->all();
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder for {$this->subscription->service->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line($this->messageText())
            ->action("For more details, please visit: ", env("FRONT_END_URL") . "/my-services/" . $this->subscription->id)
            ->line("Expire date: {$this->subscription->end_date}");
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $notificationData = [
            'subscription_id' => (string)$this->subscription->id,
            'service_name' => $this->subscription->service->name,
            'service_icon' => $this->subscription->service->icon,
            'message' => $this->messageText(),
            'end_date' => $this->subscription->end_date->toIso8601String()
        ];

        return (new FcmMessage(notification: new FcmNotification(
            title: "Service {$this->subscription->service->name}",
            body: $this->messageText(),
        )))
            ->data([
                'id' => (string)$this->id,
                'notifiable_id' => (string)$notifiable->id,
                'notification_data' => json_encode($notificationData),
                'read_at' => '',
                'created_at' => now()->toIso8601String(),
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'service_name' => $this->subscription->service->name,
            'service_icon' => $this->subscription->service->icon,
            'message' => $this->messageText(),
            'end_date' => $this->subscription->end_date
        ];
    }

    private function messageText(): string
    {
        $service = $this->subscription->service->name;
        $daysLeft = now()->diffInDays($this->subscription->end_date, false);
        $daysDisplay = floor(abs($daysLeft));

        return match (true) {
            $daysLeft > 0 => "{$service} will expire in {$daysDisplay} days.",
            $daysLeft == 0 => "{$service} expires today.",
            default => "{$service} expired {$daysDisplay} days ago.",
        };
    }
}

<?php

namespace App\Notifications;

use App\Enums\AlertChanels;
use App\Enums\SubscriptionStatus;
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
    protected SubscriptionStatus $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, SubscriptionStatus $type)
    {
        $this->subscription = $subscription;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $map = [
            AlertChanels::EMAIL->value => "mail",
            AlertChanels::IN_APP->value => "database",
            AlertChanels::PUSH->value => FcmChannel::class,
        ];

        return array_values(
            array_intersect_key($map, array_flip($this->subscription->reminder_channels ?? [])),
        );
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
            ->action("For more details, please visit: ", url("/subscriptions/" . $this->subscription->id))
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
        $hoursLeft = now()->diffInHours($this->subscription->end_date, false);
        $hoursDisplay = floor(abs($hoursLeft));

        return match (true) {
            $hoursLeft > 0 => "{$service} will expire in {$hoursDisplay} hours.",
            $hoursLeft == 0 => "{$service} expires this hour.",
            default => "{$service} expired {$hoursDisplay} hours ago.",
        };
    }
}

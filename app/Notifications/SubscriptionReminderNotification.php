<?php

namespace App\Notifications;

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
    protected string $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, string $type)
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
        $channels = [];

        $prefs = $this->subscription->reminder_channels ?? [];

        if (in_array("email", $prefs)) $channels[] = "mail";
        if (in_array("in_app", $prefs)) $channels[] = "database";
        if (in_array("push", $prefs)) $channels[] = FcmChannel::class;

        return $channels;
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
        return (new FcmMessage(notification: new FcmNotification(
            title: "Service {$this->subscription->name}",
            body: $this->messageText(),
        )))
            ->data([
                "subscription_id" => (string)$this->subscription->id,
                "type" => $this->type,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'service_id' => $this->subscription->service->id,
            'service_name' => $this->subscription->service->name,
            'message' => $this->message,
            'end_date' => $this->subscription->end_date
        ];
    }

    private function messageText(): string
    {
        $service = $this->subscription->service->name;
        $days = now()->diffInDays($this->subscription->end_date, false);

        return match (true) {
            $days > 0 => "Service {$service} will expire in {$days} days.",
            $days == 0 => "Service {$service} expires today.",
            default => "Service {$service} is expired " . abs($days) . " days.",
        };
    }
}

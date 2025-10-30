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
        $channels = [];

        $prefs = $this->subscription->reminder_channels ?? [];

        if (in_array(AlertChanels::EMAIL->value, $prefs)) $channels[] = "mail";
        if (in_array(AlertChanels::IN_APP->value, $prefs)) $channels[] = "database";
        if (in_array(AlertChanels::PUSH->value, $prefs)) $channels[] = FcmChannel::class;

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
            title: "Service {$this->subscription->service->name}",
            body: $this->messageText(),
        )))
            ->data([
                'subscription_id' => $this->subscription->id,
                'service_name' => $this->subscription->service->name,
                'service_icon' => $this->subscription->service->icon,
                'message' => $this->messageText(),
                'end_date' => $this->subscription->end_date
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

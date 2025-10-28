<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class SubscriptionReminderNotification extends Notification
{
    use Queueable;

    protected Subscription $subscription;
    protected string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, string $message)
    {
        $this->subscription = $subscription;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [FcmChannel::class, 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: "Service {$this->subscription->name}",
            body: $this->message,
        )))
            ->data([
                'service_id' => $this->subscription->service->id,
                'service_name' => $this->subscription->service->name,
                'message' => $this->message,
                'end_date' => $this->subscription->end_date
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
}

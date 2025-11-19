<?php

namespace Tests\Unit\Notification;

use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use App\Notifications\SendMessageNotification;
use App\Notifications\SubscriptionReminderNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Tests\TestCase;

class SendNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationService();
    }

    public function test_reminder_subscription_success()
    {
        FacadesNotification::fake();

        $account = Account::factory()->create();
        $sub = Subscription::factory()->create([
            'account_id' => $account->id,
            'service_id' => Service::factory()->create()
        ]);

        $sub->load('account');

        $result = $this->service->reminderSubscription([
            'subscription_ids' => [$sub->id]
        ]);

        FacadesNotification::assertSentTo(
            $account,
            SubscriptionReminderNotification::class
        );

        $this->assertEquals(['message' => 'Notification has been sent'], $result);
    }

    public function test_reminder_subscription_exception()
    {
        $this->expectException(\Exception::class);

        $this->service->reminderSubscription(['123']);
    }

    public function test_send_message_success()
    {
        FacadesNotification::fake();

        $user = Account::factory()->create(['email' => 'test@gmail.com']);

        $result = $this->service->sendMessage([
            'recipients' => ['test@gmail.com'],
            'message' => 'Hello'
        ]);

        FacadesNotification::assertSentTo(
            [$user],
            SendMessageNotification::class
        );

        $this->assertEquals(['message' => 'Message has been sent'], $result);
    }

    public function test_send_message_exception()
    {
        $this->expectException(\Exception::class);

        $this->service->sendMessage(['123']);
    }
}

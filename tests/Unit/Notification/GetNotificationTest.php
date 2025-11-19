<?php

namespace Tests\Unit\Notification;

use App\Models\Account;
use App\Models\Notification;
use App\Models\Service;
use App\Models\Subscription;
use App\Notifications\SendMessageNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GetNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationService();
    }

    public function test_get_notifications_success()
    {
        $user = Account::factory()->create();

        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => SendMessageNotification::class,
            'data' => ['msg' => 'hello']
        ]);

        $result = $this->service->get($user, ['limit' => 10]);

        $this->assertCount(1, $result);
    }

    public function test_get_notifications_exception()
    {
        $this->expectException(\Exception::class);

        $user = new Account();

        $this->service->get($user, ['limit' => "abc"]);
    }

    public function test_get_admin_log_notifications_success()
    {
        $admin = Account::factory()->create(['role' => 'admin']);

        $account = Account::factory()->create();

        $service = Service::factory()->create();

        $subscription = Subscription::factory()->create([
            'account_id' => $account->id,
            'service_id' => $service->id
        ]);

        Notification::factory()
            ->count(3)
            ->state([
                'sender_id' => $admin->id,
                'notifiable_id' => $account->id,
                'data' => [
                    'subscription_id' => $subscription->id,
                    'service_name' => $service->name,
                    'service_icon' => $service->icon,
                    'message' => 'Test message',
                    'end_date' => $subscription->end_date,
                ],
            ])
            ->create();

        $result = $this->service->getAdminLogNotifications(5);

        $this->assertEquals(0, $result->total());
    }

    public function test_get_admin_log_notifications_exception()
    {
        $this->expectException(\Exception::class);

        $this->service->getAdminLogNotifications('abc');
    }


    public function test_get_notification_by_id_success()
    {
        $user = Account::factory()->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => SendMessageNotification::class,
            'data' => ['msg' => 'Hello']
        ]);

        $found = $this->service->getById($user, $notification->id);

        $this->assertEquals($notification->id, $found->id);
    }

    public function test_get_notification_by_id_exception()
    {
        $this->expectException(\Exception::class);

        $user = Account::factory()->create();

        $this->service->getById($user, 99999);
    }
}

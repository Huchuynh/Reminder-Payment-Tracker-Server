<?php

namespace Tests\Unit\Notification;

use App\Models\Account;
use App\Notifications\SendMessageNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeleteNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationService();
    }

    public function test_delete_notification_success()
    {
        $user = Account::factory()->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => SendMessageNotification::class,
            'data' => ['msg' => 'Hi']
        ]);

        $this->service->delete($user, $notification->id);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_delete_notification_exception()
    {
        $this->expectException(\Exception::class);

        $user = Account::factory()->create();

        $this->service->delete($user, 999);
    }
}

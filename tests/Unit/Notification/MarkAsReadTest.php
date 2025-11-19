<?php

namespace Tests\Unit\Notification;

use App\Models\Account;
use App\Notifications\SendMessageNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarkAsReadTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationService();
    }

    public function test_mark_as_read_success()
    {
        $user = Account::factory()->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => SendMessageNotification::class,
            'data' => ['msg' => 'Hello']
        ]);

        $this->service->markAsRead($user, $notification->id);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_as_read_exception()
    {
        $this->expectException(\Exception::class);

        $user = Account::factory()->create();

        $this->service->markAsRead($user, 999);
    }

    public function test_mark_all_as_read_success()
    {
        $user = Account::factory()->create();

        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => SendMessageNotification::class,
            'data' => ['msg' => 'Hello']
        ]);

        $this->service->markAllAsRead($user);

        $this->assertEquals(0, $user->fresh()->unreadNotifications->count());
    }

    public function test_mark_all_as_read_exception()
    {
        $this->expectException(\Exception::class);

        $this->service->markAllAsRead("123");
    }
}

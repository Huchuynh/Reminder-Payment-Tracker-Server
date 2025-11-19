<?php

namespace Tests\Unit\Auth;

use App\Mail\OtpMail;
use App\Models\Account;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuthService();
    }

    public function test_register_success()
    {
        Mail::fake();

        $request = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $account = $this->service->register($request);

        $this->assertDatabaseHas('accounts', [
            'email' => 'john@example.com',
            'full_name' => 'John Doe',
        ]);

        $this->assertInstanceOf(Account::class, $account);

        Mail::assertSent(OtpMail::class, function ($mail) use ($request) {
            return $mail->hasTo($request['email']);
        });
    }

    public function test_register_exception_when_email_already_exists()
    {
        $this->expectException(\Exception::class);

        Account::factory()->create(['email' => 'existing@example.com']);

        $request = [
            'full_name' => 'Jane Doe',
            'email' => 'existing@example.com',
        ];

        $this->service->register($request);
    }
}

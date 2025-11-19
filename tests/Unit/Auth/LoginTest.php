<?php

namespace Tests\Unit\Auth;

use App\Enums\AccountRole;
use App\Models\Account;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuthService();
    }

    public function test_login_success()
    {
        $account = Account::factory()->create();
        $result = $this->service->login($account->email);

        $this->assertEquals($account->id, $result->id);
    }

    public function test_login_fail_invalid_email()
    {
        $this->expectException(\Exception::class);
        $this->service->login('nonexistent@example.com');
    }

    public function test_login_google_success()
    {
        $data = [
            'credentials' => 'ignored_for_test',
            'fcm_token' => 'fake_fcm_token',
        ];

        $payload = [
            'email' => 'user@example.com',
            'name' => 'John Doe',
            'picture' => 'https://example.com/avatar.jpg',
        ];

        $account = Account::updateOrCreate(
            ['email' => $payload['email']],
            [
                'full_name' => $payload['name'],
                'avatar' => $payload['picture'],
                'password' => bcrypt('password'),
                'role' => AccountRole::USER,
                'fcm_token' => $data['fcm_token'],
            ]
        );

        $this->assertDatabaseHas('accounts', ['email' => $payload['email']]);
        $this->assertEquals($account->full_name, $payload['name']);
        $this->assertEquals($account->fcm_token, $data['fcm_token']);
    }

    public function test_login_google_invalid_token()
    {
        $this->expectException(\Exception::class);

        $data = [
            'credentials' => 'invalid_token',
            'fcm_token' => 'fake_fcm_token',
        ];

        $this->service->getAccountByEmail('nonexistent@example.com');
    }

}

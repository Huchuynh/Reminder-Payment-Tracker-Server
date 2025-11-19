<?php

namespace Tests\Unit\Account;

use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAccountTest extends TestCase
{
    use RefreshDatabase;

    protected AccountService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AccountService();
    }

    public function test_it_updates_multiple_accounts_active_state()
    {
        $accounts = Account::factory()->count(3)->create(['is_active' => true]);

        $payload = [
            'account_ids' => $accounts->pluck('id')->toArray(),
            'is_active' => false,
        ];

        $this->service->updateAccountActiveState($payload);

        foreach ($accounts as $acc) {
            $this->assertDatabaseHas('accounts', ['id' => $acc->id, 'is_active' => false]);
        }
    }

    public function test_it_throws_exception_on_update_failure()
    {
        $this->expectException(\Exception::class);

        $this->service->updateAccountActiveState([
            'account_ids' => 1,
            'is_active' => false
        ]);
    }
}

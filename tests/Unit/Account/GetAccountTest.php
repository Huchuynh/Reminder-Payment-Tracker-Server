<?php

namespace Tests\Unit\Account;

use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAccountTest extends TestCase
{
    use RefreshDatabase;

    protected AccountService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AccountService();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function test_returns_paginated_accounts_matching_search()
    {
        Account::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'full_name' => 'John Wick'
        ]);

        Account::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'full_name' => 'Random Person'
        ]);

        $params = [
            'is_active' => true,
            'search' => 'John',
            'limit' => 20
        ];

        $result = $this->service->getAccountPaginated($params);

        $this->assertEquals(1, $result->total());
    }

    public function test_it_filters_accounts_by_inactive_days()
    {
        Account::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'last_active_at' => now()->subDays(30)
        ]);

        Account::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'last_active_at' => now()->subDays(5)
        ]);

        $params = [
            'is_active' => true,
            'search' => '',
            'inactive_days' => 10,
            'limit' => 20
        ];

        $result = $this->service->getAccountPaginated($params);

        $this->assertEquals(1, $result->total());
    }

    public function test_it_throws_exception_when_query_fails()
    {
        $this->expectException(\Exception::class);

        $this->service->getAccountPaginated([
            'is_active' => 56,
            'search' => '',
            'limit' => 10
        ]);
    }

    /*Get Selected Account table*/
    public function test_it_returns_only_user_role_accounts()
    {
        Account::factory()->create(['role' => 'admin']);
        Account::factory()->count(3)->create(['role' => 'user']);

        $result = $this->service->getSelectableAccounts();

        $this->assertCount(3, $result);
    }
}

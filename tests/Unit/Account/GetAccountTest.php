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
}

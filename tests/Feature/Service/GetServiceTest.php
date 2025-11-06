<?php

namespace Tests\Feature\Service;

use App\Models\Account;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $account;

    private string $prefix = "/api/services";

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = Account::factory()->create();
        $this->actingAs($this->account, 'api');
    }

    #[Test]
    public function it_returns_paginated_services_with_search_and_sorting(): void
    {
        Service::factory()->create(['name' => 'Google Drive', 'provider' => 'Google']);
        Service::factory()->create(['name' => 'Dropbox', 'provider' => 'Dropbox']);
        Service::factory()->create(['name' => 'iCloud', 'provider' => 'Apple']);

        $response = $this->getJson("{$this->prefix}?search=Google&sort_by=name&sort_order=asc&limit=10");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'provider', 'created_at', 'updated_at'],
                ],
                'links',
                'meta',
            ]);

        $response->assertJsonFragment(['name' => 'Google Drive', 'provider' => 'Google']);
    }

    #[Test]
    public function it_returns_a_single_service_by_id(): void
    {
        $service = Service::factory()->create(['name' => 'Google Drive', 'provider' => 'Google']);

        $response = $this->getJson("{$this->prefix}/{$service->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    "id",
                    "account_id",
                    "name",
                    "provider",
                    "icon",
                    "is_base",
                    "created_at",
                    "deleted_at",
                    "updated_at"
                ]
            ]);

        $response->assertJsonFragment(['name' => 'Google Drive', 'provider' => 'Google']);
    }
}

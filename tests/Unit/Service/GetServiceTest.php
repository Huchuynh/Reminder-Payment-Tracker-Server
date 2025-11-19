<?php

namespace Tests\Unit\Service;

use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServiceService();
    }

    public function test_get_returns_paginated_services_success()
    {
        $service = Service::factory()->create(['is_base' => true, 'name' => 'Netflix']);
        $params = [
            'search' => 'Netflix',
            'sort_by' => 'id',
            'sort_order' => 'asc',
            'limit' => 10
        ];

        $result = $this->service->get($params);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Netflix', $result->items()[0]->name);
    }

    public function test_get_throws_exception_when_query_fails()
    {
        $this->expectException(\Exception::class);

        $this->service->get([]);
    }

    public function test_get_base_returns_only_base_services()
    {
        $baseService = Service::factory()->create(['is_base' => true]);
        $otherService = Service::factory()->create(['is_base' => false]);

        $result = $this->service->getBase();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is_base);
    }

    public function test_find_by_id_returns_service()
    {
        $service = Service::factory()->create();

        $result = $this->service->findById($service->id);

        $this->assertEquals($service->id, $result->id);
    }

    public function test_find_by_id_throws_exception_if_not_found()
    {
        $this->expectException(\Exception::class);
        $this->service->findById(9999);
    }
}

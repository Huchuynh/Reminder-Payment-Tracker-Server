<?php

namespace Tests\Unit\Service;

use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServiceService();
    }

    public function test_create_service_success()
    {
        $data = Service::factory()->make()->toArray();

        $service = $this->service->create($data);

        $this->assertDatabaseHas('services', ['id' => $service->id]);
    }

    public function test_create_service_throws_exception()
    {
        $this->expectException(\Exception::class);

        $this->service->create(['123']);
    }
}

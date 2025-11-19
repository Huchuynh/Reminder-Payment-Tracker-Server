<?php

namespace Tests\Unit\Service;

use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServiceService();
    }

    public function test_delete_service_success()
    {
        $service = Service::factory()->create();

        $result = $this->service->delete([$service->id]);

        $this->assertEquals('Service successfully deleted', $result['message']);
    }

    public function test_delete_service_throws_exception()
    {
        $this->expectException(\Exception::class);

        $this->service->delete(['abc']);
    }
}

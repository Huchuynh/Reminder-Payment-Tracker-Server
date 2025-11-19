<?php

namespace Tests\Unit\Service;

use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServiceService();
    }

    public function test_update_service_success()
    {
        $service = Service::factory()->create(['name' => 'Old Name']);
        $data = ['name' => 'New Name'];

        $updated = $this->service->update($service->id, $data);

        $this->assertEquals('New Name', $updated->name);
    }

    public function test_update_service_throws_exception()
    {
        $this->expectException(\Exception::class);

        $this->service->update(9999, ['123']);
    }
}

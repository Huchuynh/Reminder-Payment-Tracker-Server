<?php

namespace Service;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $account;
    private string $prefix = "/api/services/";

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = Account::factory()->create();
        $this->actingAs($this->account, 'api');
    }

    #[Test]
    public function it_can_create_a_service(): void
    {
        $payload = [
            'account_id' => $this->account->id,
            'name' => "Test Service",
            'provider' => "Test",
            'is_base' => false,
        ];;

        $response = $this->postJson($this->prefix, $payload);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Service successfully created']);

        $this->assertDatabaseHas('services', $payload);
    }
}

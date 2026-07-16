<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Services\ServerProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_service_requires_auth(): void
    {
        $response = $this->get('/client/services');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_services(): void
    {
        Service::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get('/client/services');

        $response->assertStatus(200);
    }

    public function test_service_show_page(): void
    {
        $service = Service::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get("/client/services/{$service->id}");

        $response->assertStatus(200);
    }

    public function test_provisioning_fails_without_server_type(): void
    {
        $service = Service::factory()->create([
            'user_id' => $this->user->id,
            'server_extension' => null,
            'status' => 'pending',
        ]);

        $provisioning = new ServerProvisioningService;
        $result = $provisioning->provision($service);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Server extension not found', $result['error']);
    }
}

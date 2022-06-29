<?php

namespace Tests\Feature\Client;

use App\Enums\PermissionsEnum;
use App\Models\Client;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DeleteClientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Client|Collection|Model
     */
    public $client;

    /**
     * @var Workspace|Collection|Model
     */
    public $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->workspace = $this->user->workspaces()->first();

        $this->client = Client::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::DELETE_CLIENTS->value);
    }

    /**
     * @test
     */
    public function user_can_delete_client()
    {
        $this->assertDatabaseHas('clients', [
            'id' => $this->client->id,
        ]);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('clients', [
            'id' => $this->client->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_delete_project()
    {
        $this->user->revokePermissionTo(PermissionsEnum::DELETE_CLIENTS->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @return TestResponse
     */
    public function destroy()
    {
        $route = route('client.destroy', $this->user->workspace_id);

        return $this->actingAs($this->user)
            ->delete($route, ['ids' => [$this->client->id]]);
    }
}

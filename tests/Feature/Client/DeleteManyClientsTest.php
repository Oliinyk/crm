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

class DeleteManyClientsTest extends TestCase
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

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::DELETE_CLIENTS->value);
    }

    /**
     * @test
     */
    public function user_can_delete_many_clients_at_once()
    {
        $client = Client::factory()->create(['workspace_id' => $this->workspace->id]);

        $client1 = Client::factory()->create(['workspace_id' => $this->workspace->id]);

        $this->destroyMany(['ids' => [$client->id, $client1->id]])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('groups', [
            'id' => $client->id,
        ]);

        $this->assertDatabaseMissing('groups', [
            'id' => $client1->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_delete_clients()
    {
        $this->user->revokePermissionTo(PermissionsEnum::DELETE_CLIENTS->value);

        $this->destroyMany()->assertStatus(403);
    }

    /**
     * @test
     */
    public function ids_field_is_required()
    {
        $this->destroyMany(['ids' => ''])
            ->assertStatus(302)
            ->assertSessionHasErrors(['ids' => 'The ids field is required.']);
    }

    /**
     * @test
     */
    public function ids_field_must_be_an_array()
    {
        $this->destroyMany(['ids' => 'test'])
            ->assertStatus(302)
            ->assertSessionHasErrors(['ids' => 'The ids must be an array.']);
    }

    /**
     * @test
     */
    public function client_id_must_exist()
    {
        $this->destroyMany(['ids' => ['123']])
            ->assertStatus(302)
            ->assertSessionHasErrors(['ids.0' => 'The selected ids.0 is invalid.']);
    }

    /**
     * @test
     */
    public function client_id_must_exist_in_a_current_workspace()
    {
        $client = Client::factory()->create();

        $this->destroyMany(['ids' => [$client->id]])
            ->assertStatus(302)
            ->assertSessionHasErrors(['ids.0' => 'The selected ids.0 is invalid.']);
    }

    /**
     * @return TestResponse
     */
    public function destroyMany($overwrites = [])
    {
        return $this->actingAs($this->user)
            ->delete(route('client.destroy', $this->workspace->id), array_merge([], $overwrites));
    }
}

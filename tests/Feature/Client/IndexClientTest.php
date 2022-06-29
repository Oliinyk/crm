<?php

namespace Tests\Feature\Client;

use App\Enums\PermissionsEnum;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class IndexClientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Client
     */
    public $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->client = Client::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);

        $this->user->givePermissionTo(PermissionsEnum::SEE_CLIENTS->value);
    }

    /**
     * @test
     */
    public function user_can_see_clients()
    {
        $this->index()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Clients/Index')
                ->has('clients.data', 1)
                ->has('clients.data.0', fn (Assert $page) => $page
                    ->where('id', $this->client->id)
                    ->where('name', $this->client->name)
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_can_search_clients_by_name()
    {
        Client::factory()->count(10)->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->client->update([
            'name' => 'lol kek',
        ]);

        $this->index(['search' => 'lol kek'])
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Clients/Index')
                ->has('clients.data', 1)
                ->has('clients.data.0', fn (Assert $page) => $page
                    ->where('id', $this->client->id)
                    ->where('name', $this->client->name)
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_can_search_clients_by_email()
    {
        Client::factory()->count(10)->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->client->update([
            'email' => 'lol_kek@example.com',
        ]);

        $this->index(['search' => 'lol_kek@example.com'])
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Clients/Index')
                ->has('clients.data', 1)
                ->has('clients.data.0', fn (Assert $page) => $page
                    ->where('id', $this->client->id)
                    ->where('name', $this->client->name)
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_can_search_clients_by_city()
    {
        Client::factory()->count(10)->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->client->update([
            'city' => 'cocnove',
        ]);

        $this->index(['search' => 'cocnove'])
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Clients/Index')
                ->has('clients.data', 1)
                ->has('clients.data.0', fn (Assert $page) => $page
                    ->where('id', $this->client->id)
                    ->where('name', $this->client->name)
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_see_clients()
    {
        $this->user->revokePermissionTo(PermissionsEnum::SEE_CLIENTS->value);

        $this->index()->assertStatus(403);
    }

    /**
     * @return TestResponse
     */
    public function index($overwrites = [])
    {
        return $this->actingAs($this->user)
            ->get(route('client.index', array_merge(['workspace' => $this->user->workspace_id], $overwrites)));
    }
}

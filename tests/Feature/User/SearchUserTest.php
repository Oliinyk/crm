<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SearchUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Workspace|Collection|Model
     */
    public $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');


        $this->user = User::factory()->create();

        $this->workspace = $this->user->workspaces()->first();

        User::factory()->count(9)->create([
            'workspace_id' => $this->workspace->id,
        ])->each(fn ($user) => $this->workspace->members()->attach($user));

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
    }

    /**
     * @test
     */
    public function user_can_search_user_by_name()
    {
        $this->user->update(['full_name' => 'lol kek']);

        $this->search(['search' => 'lol kek'])
            ->assertStatus(200)
            ->assertJsonFragment([
                'data' => [
                    [
                        'id' => $this->user->id,
                        'name' => $this->user->full_name,
                        'image' => [
                            'name' => null,
                            'size' => null,
                            'type' => null,
                            'url' => '',
                        ],
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data');
    }

    /**
     * @test
     */
    public function user_can_search_user_by_name_with_excluded_selected_options()
    {
        $this->user->update(['title' => 'lol kek']);

        User::factory()->count(9)->create([
            'full_name' => 'lol kek',
        ])->each(fn ($user) => $this->workspace->members()->attach($user));

        $this->search([
            'search' => 'lol kek',
            'selectedOptions' => [$this->user->toJson()],
        ])->assertStatus(200)
            ->assertJsonCount(9, 'data');
    }

    public function search($overwrites = [])
    {
        $route = route('user.search', array_merge([
            'workspace' => $this->user->workspace_id
        ], $overwrites));

        return $this->actingAs($this->user)->get($route);
    }
}

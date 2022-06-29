<?php

namespace Tests\Feature\Layer;

use App\Models\Layer;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SearchLayerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Project|Collection|Model
     */
    public $project;

    /**
     * @var Workspace|Collection|Model
     */
    public $workspace;

    /**
     * @var Layer|Collection|Model
     */
    public $layer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();

        $this->workspace = $this->user->workspaces()->first();

        $this->project = Project::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $this->layer = Layer::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $this->project->id,
        ]);

        Layer::factory()->count(9)->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $this->project->id,
        ]);

        $this->user->removeRole('Administrator');
        $this->project->members()->attach($this->user->id);
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
    }

    /**
     * @test
     */
    public function user_can_search_layer_by_title()
    {
        $this->layer->update(['title' => 'lol kek']);

        $this->search(['search' => 'lol kek'])
            ->assertStatus(200)
            ->assertJsonFragment([
                'data' => [
                    [
                        'id' => $this->layer->id,
                        'name' => $this->layer->title,
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data');
    }

    /**
     * @test
     */
    public function user_can_search_layer_by_title_with_excluded_selected_options()
    {
        $this->layer->update(['title' => 'lol kek']);

        Layer::factory()->count(9)->create([
            'title' => 'lol kek',
            'workspace_id' => $this->workspace->id,
            'project_id' => $this->project->id,
        ]);

        $this->search([
            'search' => 'lol kek',
            'selectedOptions' => [$this->layer->toJson()],
        ])->assertStatus(200)
            ->assertJsonCount(9, 'data');
    }

    public function search($overwrites = [])
    {
        $route = route('layer.search', array_merge([
            'workspace' => $this->workspace->id,
            'project' => $this->project->id,
        ], $overwrites));

        return $this->actingAs($this->user)->get($route);
    }
}

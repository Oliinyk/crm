<?php

namespace Tests\Feature\Layer;

use App\Models\Layer;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class IndexLayerTest extends TestCase
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

        $this->project->members()->attach($this->user->id);

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
    }

    /**
     * @test
     */
    public function user_can_see_layers()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->layer->addMedia($file)->toMediaCollection();

        $this->index()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Layers/Index')
                ->has('layers.data', 10)
                ->has('layers.data.0', fn (Assert $page) => $page
                    ->where('id', $this->layer->id)
                    ->where('title', $this->layer->title)
                    ->has('media', 1)
                    ->has('media.0', fn (Assert $page) => $page
                        ->where('file_name', 'avatar.jpg')
                        ->etc()
                    )
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_can_search_layer_by_title()
    {
        $this->layer->update(['title' => 'lol kek']);

        $this->search(['search' => 'lol kek']);
    }

    /**
     * @test
     */
    public function user_can_search_layer_by_media_name()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->layer->addMedia($file)->toMediaCollection();

        $this->search(['search' => 'avatar.jpg']);
    }

    /**
     * @return TestResponse
     */
    public function index()
    {
        $route = route('layer.index', [
            'workspace' => $this->workspace,
            'project' => $this->project,
        ]);

        return $this->actingAs($this->user)
            ->get($route);
    }

    public function search($overwrites = [])
    {
        $route = route('layer.index', array_merge([
            'workspace' => $this->workspace,
            'project' => $this->project,
        ], $overwrites));

        $this->actingAs($this->user)->get($route)
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Layers/Index')
                ->has('layers.data', 1)
                ->has('layers.data.0', fn (Assert $page) => $page
                    ->where('id', $this->layer->id)
                    ->where('title', $this->layer->title)
                    ->etc()
                )
            );
    }
}

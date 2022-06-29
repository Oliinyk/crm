<?php

namespace Tests\Feature\Project;

use App\Enums\PermissionsEnum;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class IndexProjectTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->workspace = $this->user->workspaces()->first();
        $this->project = Project::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        Project::factory()->count(5)->create();

        Project::factory()->count(5)->create()->each(function (Project $project) {
            $project->members()->attach($this->user->id);
        });

        $this->project->members()->attach($this->user->id);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
    }

    /**
     * @test
     */
    public function user_can_see_projects()
    {
        $this->index()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Index')
                ->has('projects.data', $this->workspace->projects()->count())
            );
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_see_all_projects()
    {
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);

        $this->index()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_see_joined_projects()
    {

        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);

        $this->user->givePermissionTo(PermissionsEnum::SEE_JOINED_PROJECTS->value);

        $this->index()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Index')
                ->has('projects.data', $this->user->projects()->count())
            );

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->revokePermissionTo(PermissionsEnum::SEE_JOINED_PROJECTS->value);
        $this->index()->assertStatus(403);
    }

    /**
     * @test
     */
    public function the_user_should_only_see_projects_where_he_is_a_member_if_he_has_access_to_it()
    {
        Project::factory()->count(5)->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $this->index()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Index')
                ->has('projects.data', $this->workspace->projects()->count()));

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);

        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);

        $this->user->givePermissionTo(PermissionsEnum::SEE_JOINED_PROJECTS->value);

        $this->index()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Index')
                ->has('projects.data', $this->user->projects()->count())
            );
    }

    /**
     * @test
     */
    public function it_must_show_permission_if_user_can_create_projects()
    {
        $this->index()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Index')
                ->has('can', fn (Assert $page) => $page->where('create_project', false))
            );

        $this->user->givePermissionTo(PermissionsEnum::CREATE_PROJECTS->value);

        $this->index()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Index')
                ->has('can', fn (Assert $page) => $page->where('create_project', true))
            );
    }

    /**
     * @return TestResponse
     */
    public function index()
    {
        return $this->actingAs($this->user)
            ->get(route('project.index', ['workspace' => $this->workspace]));
    }
}

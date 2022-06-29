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
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ShowProjectTest extends TestCase
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

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
    }

    /**
     * @test
     */
    public function user_can_see_project()
    {
        $this->show()->assertStatus(200);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_see_the_project()
    {
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);

        $this->show()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_see_the_joined_project()
    {
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);

        $this->user->projects()->attach($this->project->id);

        $this->user->givePermissionTo(PermissionsEnum::SEE_JOINED_PROJECTS->value);

        $this->show()->assertStatus(200);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->revokePermissionTo(PermissionsEnum::SEE_JOINED_PROJECTS->value);

        $this->show()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_can_see_deleted_project()
    {
        $this->project->delete();

        $this->show()->assertStatus(200);
    }

    /**
     * @return TestResponse
     */
    public function show()
    {
        return $this->actingAs($this->user)
            ->get(route('project.show', [
                'workspace' => $this->workspace,
                'project' => $this->project
            ]));
    }
}

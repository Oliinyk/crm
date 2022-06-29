<?php

namespace Tests\Feature\Project;

use App\Enums\PermissionsEnum;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DeleteProjectTest extends TestCase
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
        $this->user->givePermissionTo(PermissionsEnum::DELETE_ALL_PROJECTS->value);
    }

    /**
     * @test
     */
    public function user_can_delete_project()
    {
        Carbon::setTestNow('2020-01-01');

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'deleted_at' => now(),
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_delete_project()
    {
        $this->user->revokePermissionTo(PermissionsEnum::DELETE_ALL_PROJECTS->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_without_permission_can_delete_own_project()
    {
        $this->project->update([
            'owner_id' => $this->user->id,
        ]);

        $this->user->revokePermissionTo(PermissionsEnum::DELETE_ALL_PROJECTS->value);
        $this->user->givePermissionTo(PermissionsEnum::DELETE_OWN_PROJECTS->value);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'deleted_at' => now(),
        ]);
    }

    /**
     * @test
     */
    public function user_without_any_permission_cant_delete_even_own_project()
    {
        $this->project->update([
            'owner_id' => $this->user->id,
        ]);

        $this->user->revokePermissionTo(PermissionsEnum::DELETE_ALL_PROJECTS->value);
        $this->user->revokePermissionTo(PermissionsEnum::DELETE_OWN_PROJECTS->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @return TestResponse
     */
    public function destroy()
    {
        return $this->actingAs($this->user)
            ->delete(route('project.destroy', [
                'project' => $this->project,
                'workspace' => $this->workspace
            ]));
    }
}

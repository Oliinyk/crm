<?php

namespace Tests\Feature\Layer;

use App\Enums\PermissionsEnum;
use App\Models\Layer;
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

class DeleteLayerTest extends TestCase
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

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::DELETE_ALL_LAYERS->value);
    }

    /**
     * @test
     */
    public function user_can_delete_a_layer()
    {
        $this->assertDatabaseHas('layers', [
            'id' => $this->layer->id,
        ]);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('layers', [
            'id' => $this->layer->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_delete_layer()
    {
        $this->user->revokePermissionTo(PermissionsEnum::DELETE_ALL_LAYERS->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_without_permission_can_delete_own_project()
    {
        $this->layer->update([
            'author_id' => $this->user->id,
        ]);

        $this->user->revokePermissionTo(PermissionsEnum::DELETE_ALL_LAYERS->value);
        $this->user->givePermissionTo(PermissionsEnum::DELETE_OWN_LAYERS->value);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function user_without_any_permission_cant_delete_even_own_project()
    {
        $this->layer->update([
            'author_id' => $this->user->id,
        ]);

        $this->user->revokePermissionTo(PermissionsEnum::DELETE_ALL_LAYERS->value);
        $this->user->revokePermissionTo(PermissionsEnum::DELETE_OWN_LAYERS->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @return TestResponse
     */
    public function destroy()
    {
        $route = route('layer.destroy', [
            'workspace' => $this->workspace->id,
            'project' => $this->project->id,
            'layer' => $this->layer->id
        ]);

        return $this->actingAs($this->user)
            ->delete($route);
    }
}

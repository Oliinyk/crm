<?php

namespace Tests\Feature\Workspace;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public $user;

    /**
     * @var Workspace|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        User::unsetEventDispatcher();

        Storage::fake('public');

        $this->workspace = Workspace::factory()->create();
        $this->user = User::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);
        $this->user->workspaces()->attach($this->workspace);
    }

    /**
     * @test
     */
    public function user_can_delete_workspace()
    {
        $anotherWorkspace = Workspace::factory()->create();
        $this->user->workspaces()->attach($anotherWorkspace);

        $this->assertDatabaseHas('workspaces', [
            'id' => $this->workspace->id,
        ]);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('workspaces', [
            'id' => $this->workspace->id,
        ]);
    }

    /**
     * @test
     */
    public function user_can_not_delete_the_workspace_if_it_is_the_last_users_workspace()
    {
        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasErrors(['workspace_count' => 'The workspace count must be at least 2.']);

        $this->assertDatabaseHas('workspaces', [
            'id' => $this->workspace->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_change_current_workspace_id()
    {
        $anotherWorkspace = Workspace::factory()->create();
        $this->user->workspaces()->attach($anotherWorkspace);

        $this->assertDatabaseMissing('users', [
            'workspace_id' => $anotherWorkspace->id,
        ]);

        $this->assertDatabaseHas('users', [
            'workspace_id' => $this->workspace->id,
        ]);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('users', [
            'workspace_id' => $this->workspace->id,
        ]);

        $this->assertDatabaseHas('users', [
            'workspace_id' => $anotherWorkspace->id,
        ]);
    }

    /**
     * @return \Illuminate\Testing\TestResponse
     */
    public function destroy()
    {
        return $this->actingAs($this->user)
            ->delete(route('workspace.destroy', $this->workspace));
    }
}

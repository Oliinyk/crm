<?php

namespace Tests\Feature\Group;

use App\Enums\PermissionsEnum;
use App\Models\Group;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DeleteGroupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Group|Collection|Model
     */
    public $group;

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

        $this->group = Group::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);

        $this->user->givePermissionTo(PermissionsEnum::MANAGE_GROUPS->value);
    }

    /**
     * @test
     */
    public function user_can_delete_group()
    {
        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('groups', [
            'id' => $this->group->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_delete_project()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_GROUPS->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @test
     */
    public function it_can_delete_all_group_members()
    {
        $this->group->members()->attach($this->user->id);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('members', [
            'user_id' => $this->user->id,
            'member_id' => $this->group->id,
            'member_type' => Group::class,
        ]);
    }

    /**
     * @return TestResponse
     */
    public function destroy()
    {
        return $this->actingAs($this->user)
            ->delete(route('group.destroy', ['workspace' => $this->workspace->id, 'group' => $this->group]));
    }
}

<?php

namespace Tests\Feature\Role;

use App\Enums\PermissionsEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DeleteRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Role
     */
    public $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);

        $this->user->givePermissionTo(PermissionsEnum::DELETE_ROLES->value);

        $adminRole = Role::create([
            'name' => 'Moderator',
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->user->assignRole($adminRole);

        $this->role = $adminRole;
    }

    /**
     * @test
     */
    public function user_can_delete_role()
    {
        $this->role = Role::create(['name' => 'test']);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('roles', [
            'id' => $this->role->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_delete_a_role()
    {
        $this->user->revokePermissionTo(PermissionsEnum::DELETE_ROLES->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_cant_delete_role_with_attached_users()
    {
        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHas('error', 'Role can not be deleted because it has users.')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('roles', [
            'id' => $this->role->id,
        ]);
    }

    /**
     * @return TestResponse
     */
    public function destroy()
    {
        return $this->actingAs($this->user)
            ->delete(route('role.destroy', [
                'role' => $this->role,
                'workspace' => $this->user->workspace_id
            ]));
    }
}

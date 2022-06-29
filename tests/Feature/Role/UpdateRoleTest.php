<?php

namespace Tests\Feature\Role;

use App\Enums\PermissionsEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UpdateRoleTest extends TestCase
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

        $this->user->givePermissionTo(PermissionsEnum::ADD_ROLES->value);

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
    public function user_can_update_role()
    {
        $this->update()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('roles', [
            'id' => $this->role->id,
            'name' => 'test',
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_update_a_role()
    {
        $this->user->revokePermissionTo(PermissionsEnum::ADD_ROLES->value);

        $this->update()->assertStatus(403);
    }

    /**
     * @test
     */
    public function the_name_field_is_required()
    {
        $this->update(['name' => ''])
            ->assertSessionHasErrors(['name' => 'The name field is required.']);
    }

    /**
     * @test
     */
    public function the_name_field_must_be_unique_in_current_workspace()
    {
        Role::create(['name' => 'test']);

        $this->update(['name' => 'test'])
            ->assertSessionHasErrors(['name' => 'The name has already been taken.']);
    }

    /**
     * @test
     */
    public function the_name_field_may_not_be_greater_than_50_characters()
    {
        $this->update(['name' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['name' => 'The name must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function see_projects_value_must_be_valid()
    {
        $this->update(['see_projects' => 'test'])
            ->assertSessionHasErrors(['see_projects' => 'The selected see projects is invalid.']);
    }

    /**
     * @test
     */
    public function create_projects_must_be_valid()
    {
        $this->update(['create_projects' => 'test'])
            ->assertSessionHasErrors(['create_projects' => 'The selected create projects is invalid.']);
    }

    /**
     * @test
     */
    public function delete_projects_value_must_be_valid()
    {
        $this->update(['delete_projects' => 'test'])
            ->assertSessionHasErrors(['delete_projects' => 'The selected delete projects is invalid.']);
    }

    /**
     * @test
     */
    public function edit_all_projects_must_be_valid()
    {
        $this->update(['edit_all_projects' => 'test'])
            ->assertSessionHasErrors(['edit_all_projects' => 'The selected edit all projects is invalid.']);
    }

    /**
     * @test
     */
    public function user_cant_update_own_role_related_permissions()
    {
        $this->update(['see_roles' => null])
            ->assertSessionHasErrors(['see_roles' => 'The see roles field is required.']);

        $this->update(['add_roles' => null])
            ->assertSessionHasErrors(['add_roles' => 'The add roles field is required.']);

        $this->update(['delete_roles' => null])
            ->assertSessionHasErrors(['delete_roles' => 'The delete roles field is required.']);
    }

    /**
     * @return TestResponse
     */
    public function update($overwrites = [])
    {
        return $this->actingAs($this->user)
            ->put(route('role.update',  [
                'role' => $this->role,
                'workspace' => $this->user->workspace_id
            ]), array_merge([
                'name' => 'test',
                'see_projects' => PermissionsEnum::SEE_ALL_PROJECTS->value,
                'create_projects' => PermissionsEnum::CREATE_PROJECTS->value,
                'edit_all_projects' => PermissionsEnum::EDIT_ALL_PROJECTS->value,
                'delete_projects' => PermissionsEnum::DELETE_ALL_PROJECTS->value,
                'see_roles' => PermissionsEnum::SEE_ROLES->value,
                'add_roles' => PermissionsEnum::ADD_ROLES->value,
                'delete_roles' => PermissionsEnum::DELETE_ROLES->value,
            ], $overwrites));
    }
}

<?php

namespace Tests\Feature\Role;

use App\Enums\PermissionsEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CreateRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->givePermissionTo(PermissionsEnum::ADD_ROLES->value);
    }

    /**
     * @test
     */
    public function user_can_create_role()
    {
        $this->create()->assertStatus(302)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('roles', [
            'name' => 'test',
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_a_role()
    {
        $this->user->revokePermissionTo(PermissionsEnum::ADD_ROLES->value);

        $this->create()->assertStatus(403);
    }

    /**
     * @test
     */
    public function the_name_field_is_required()
    {
        $this->create(['name' => ''])
            ->assertSessionHasErrors(['name' => 'The name field is required.']);
    }

    /**
     * @test
     */
    public function the_name_field_must_be_unique_in_current_workspace()
    {
        $this->create(['name' => 'test'])
            ->assertSessionDoesntHaveErrors();

        $this->create(['name' => 'test'])
            ->assertSessionHasErrors(['name' => 'The name has already been taken.']);
    }

    /**
     * @test
     */
    public function the_name_may_not_be_greater_than_50_characters()
    {
        $this->create(['name' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['name' => 'The name must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function see_projects_value_must_be_valid()
    {
        $this->create(['see_projects' => 'test'])
            ->assertSessionHasErrors(['see_projects' => 'The selected see projects is invalid.']);
    }

    /**
     * @test
     */
    public function create_projects_must_be_valid()
    {
        $this->create(['create_projects' => 'test'])
            ->assertSessionHasErrors(['create_projects' => 'The selected create projects is invalid.']);
    }

    /**
     * @test
     */
    public function edit_all_projects_must_be_valid()
    {
        $this->create(['edit_all_projects' => 'test'])
            ->assertSessionHasErrors(['edit_all_projects' => 'The selected edit all projects is invalid.']);
    }

    /**
     * @test
     */
    public function delete_projects_value_must_be_valid()
    {
        $this->create(['delete_projects' => 'test'])
            ->assertSessionHasErrors(['delete_projects' => 'The selected delete projects is invalid.']);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        return $this->actingAs($this->user)
            ->post(route('role.store', $this->user->workspace_id), array_merge([
                'name' => 'test',
                'see_projects' => PermissionsEnum::SEE_ALL_PROJECTS->value,
                'create_projects_boolean' => false,
                'edit_all_projects_boolean' => false,
                'delete_projects' => PermissionsEnum::DELETE_ALL_PROJECTS->value,
            ], $overwtites));
    }
}

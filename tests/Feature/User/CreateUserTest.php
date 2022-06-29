<?php

namespace Tests\Feature\User;

use App\Models\User;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    /**
     * @test
     */
    public function after_creating_a_user_add_an_own_workspace()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('workspaces', [
            'owner_id' => $user->id,
        ]);
    }

    /**
     * @test
     */
    public function after_creating_a_user_add_him_as_a_member_to_the_workspace()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('members', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * @test
     */
    public function after_creating_a_user_add_current_workspace_id_to_him()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'workspace_id' => $user->ownWorkspaces->first()->id,
        ]);
    }

    /**
     * @test
     */
    public function after_creating_a_user_add_an_admin_role_to_him()
    {
        $user = User::factory()->create();

        /**
         * Check if the new role has been created.
         */
        $this->assertDatabaseHas('roles', [
            'workspace_id' => $user->workspace_id,
            'name' => 'Administrator',
        ]);

        /**
         * Check if the User is added to the admin role.
         */
        $this->assertDatabaseHas('model_has_roles', [
            'workspace_id' => $user->workspace_id,
            'model_id' => $user->id,
        ]);
    }
}

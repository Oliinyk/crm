<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::whereEmail('johndoe@example.com')->first();

        Workspace::factory()
            ->count(3)
            ->create(['owner_id' => $user->id])
            ->each(function (Workspace $workspace) use ($user) {

                /**
                 * Create adn attach Admin role in current workspace for user.
                 */
                $adminRole = $this->createAdminRole($workspace);

                /**
                 * Assign admin role to the user for current workspace.
                 */
                $user->assignRole($adminRole);

                /**
                 * Attach current user to the workspace.
                 */
                $workspace->members()->attach($user->id);

                /**
                 * Get some random users.
                 */
                $workspaceMembers = User::whereDoesntHave('workspaces', fn (Builder $query) => $query->whereId($workspace->id))
                    ->inRandomOrder()
                    ->limit(15)
                    ->get();
                /**
                 * Attach admin roles
                 */
                $workspaceMembers->each(fn (User $user) => $user->assignRole($adminRole));

                $workspaceMembers = $workspaceMembers->pluck('id');

                /**
                 * Attach them to the workspace.
                 */
                $workspace->members()->attach($workspaceMembers);
            });
    }

    /**
     * Create Admin role for the current workspace.
     *
     * @param Workspace $workspace
     * @return Role
     */
    public function createAdminRole(Workspace $workspace): Role
    {
        /**
         * Create Admin role.
         * @var Role $adminRole
         */
        $adminRole = Role::create([
            'name' => 'Administrator',
            'workspace_id' => $workspace->id,
        ]);

        /*
         * Give permissions to the Admin role.
         */
        $adminRole->givePermissionTo([
            PermissionsEnum::CREATE_PROJECTS->value,
            PermissionsEnum::SEE_ALL_PROJECTS->value,
            PermissionsEnum::EDIT_ALL_PROJECTS->value,
            PermissionsEnum::DELETE_ALL_PROJECTS->value,
        ]);

        /**
         * Set the new workspace as a current workspace.
         */
        app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);

        return $adminRole;
    }
}

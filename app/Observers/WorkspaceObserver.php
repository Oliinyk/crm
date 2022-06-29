<?php

namespace App\Observers;

use App\Enums\PermissionsEnum;
use App\Models\Workspace;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class WorkspaceObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param Workspace $workspace
     * @return void
     */
    public function created(Workspace $workspace)
    {
        /**
         * Attach owner as a member to the workspace.
         */
        $workspace->members()->attach($workspace->owner->id);

        /**
         * Create Admin role.
         * @var Role $adminRole
         */
        $adminRole = Role::create([
            'name' => 'Administrator',
            'workspace_id' => $workspace->id,
        ]);

        /*
         * Give all permissions to the Admin role.
         */
        $adminRole->givePermissionTo(PermissionsEnum::values());

        /**
         * Set the new workspace as a current permission workspace.
         */
        app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);

        /**
         * Assign admin role to the user for current workspace.
         */
        $workspace->owner->assignRole($adminRole);

        /**
         * Set new workspace as current user workspace.
         */
        $workspace->owner->update([
            'workspace_id' => $workspace->id,
        ]);
    }
}

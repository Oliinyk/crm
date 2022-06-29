<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user)
    {
        /**
         * Create default user's workspace.
         * @var Workspace $workspace
         */
        $workspace = $user->ownWorkspaces()->create([
            'name' => 'Personal workspace',
            'plan' => 'personal',
        ]);

        /**
         * Add Default user's workspace as current workspace.
         */
        $user->update([
            'workspace_id' => $workspace->id,
        ]);

        /**
         * If auth User create a new User - Set old Workspace as Permission Workspace.
         */
        if (Auth::check() && request()->has('workspace')) {
            app(PermissionRegistrar::class)
                ->setPermissionsTeamId(request()->workspace->id);
        }
    }
}

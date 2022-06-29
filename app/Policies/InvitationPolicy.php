<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class InvitationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_INVITATIONS->value);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Invitation $invitation
     * @return Response|bool
     */
    public function delete(User $user, Invitation $invitation)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_INVITATIONS->value);
    }
}

<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class GroupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_GROUPS->value);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Group $group
     * @return Response|bool
     */
    public function view(User $user, Group $group)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_GROUPS->value);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Group $group
     * @return Response|bool
     */
    public function update(User $user, Group $group)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_GROUPS->value);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Group $group
     * @return Response|bool
     */
    public function delete(User $user, Group $group)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_GROUPS->value);
    }
}

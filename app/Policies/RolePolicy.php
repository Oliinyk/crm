<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Role;

class RolePolicy
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
        return $user->hasPermissionTo(PermissionsEnum::SEE_ROLES->value);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Role $role
     * @return Response|bool
     */
    public function view(User $user, Role $role)
    {
        return $user->hasPermissionTo(PermissionsEnum::SEE_ROLES->value);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionsEnum::ADD_ROLES->value);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Role $role
     * @return Response|bool
     */
    public function update(User $user, Role $role)
    {
        return $user->hasPermissionTo(PermissionsEnum::ADD_ROLES->value);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Role $role
     * @return Response|bool
     */
    public function delete(User $user, Role $role)
    {
        return $user->hasPermissionTo(PermissionsEnum::DELETE_ROLES->value);
    }
}

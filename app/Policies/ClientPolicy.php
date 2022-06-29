<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ClientPolicy
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
        return $user->hasPermissionTo(PermissionsEnum::SEE_CLIENTS->value);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Client $client
     * @return Response|bool
     */
    public function view(User $user, Client $client)
    {
        return $user->hasPermissionTo(PermissionsEnum::SEE_CLIENTS->value);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionsEnum::ADD_CLIENTS->value);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Client $client
     * @return Response|bool
     */
    public function update(User $user, Client $client)
    {
        return $user->hasPermissionTo(PermissionsEnum::ADD_CLIENTS->value);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function delete(User $user)
    {
        return $user->hasPermissionTo(PermissionsEnum::DELETE_CLIENTS->value);
    }
}

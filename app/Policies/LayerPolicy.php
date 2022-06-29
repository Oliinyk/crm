<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Layer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class LayerPolicy
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
        return $user->hasPermissionTo(PermissionsEnum::CREATE_LAYERS->value);
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Layer $layer
     * @return Response|bool
     */
    public function update(User $user, Layer $layer)
    {
        return $user->hasPermissionTo(PermissionsEnum::EDIT_ALL_LAYERS->value) || $layer->author->id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Layer $layer
     * @return Response|bool
     */
    public function delete(User $user, Layer $layer)
    {
        if ($user->hasPermissionTo(PermissionsEnum::DELETE_ALL_LAYERS->value)) {
            return true;
        }

        return $user->can(PermissionsEnum::DELETE_OWN_LAYERS->value) && $layer->author_id == $user->id;
    }
}

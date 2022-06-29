<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
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
        return $user->hasAnyPermission([
            PermissionsEnum::SEE_ALL_PROJECTS->value,
            PermissionsEnum::SEE_JOINED_PROJECTS->value,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Project $project
     * @return Response|bool
     */
    public function view(User $user, Project $project)
    {
        if ($user->can(PermissionsEnum::SEE_ALL_PROJECTS->value)) {
            return true;
        }

        return $user->projects()
                ->where('projects.id', $project->id)
                ->exists() &&
            $user->can(PermissionsEnum::SEE_JOINED_PROJECTS->value);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->can(PermissionsEnum::CREATE_PROJECTS->value);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Project $project
     * @return Response|bool
     */
    public function update(User $user, Project $project)
    {
        return $user->can(PermissionsEnum::EDIT_ALL_PROJECTS->value) || $project->owner_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Project $project
     * @return Response|bool
     */
    public function delete(User $user, Project $project)
    {
        if ($user->can(PermissionsEnum::DELETE_ALL_PROJECTS->value)) {
            return true;
        }

        return $user->can(PermissionsEnum::DELETE_OWN_PROJECTS->value) && $project->owner_id == $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Project $project
     * @return Response|bool
     */
    public function restore(User $user, Project $project)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Project $project
     * @return Response|bool
     */
    public function forceDelete(User $user, Project $project)
    {
        //
    }
}

<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TicketPolicy
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
            PermissionsEnum::SEE_ALL_TICKETS->value,
            PermissionsEnum::SEE_JOINED_TICKETS->value,
        ]);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionsEnum::CREATE_TICKETS->value);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Ticket $ticket
     * @return Response|bool
     */
    public function view(User $user, Ticket $ticket)
    {
        if ($user->hasPermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value)) {
            return true;
        }

        if ($user->hasPermissionTo(PermissionsEnum::SEE_JOINED_TICKETS->value)) {
            return $ticket->author_id == $user->id || $ticket->assignee_id == $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Ticket $ticket
     * @return Response|bool
     */
    public function update(User $user, Ticket $ticket)
    {
        if ($user->hasPermissionTo(PermissionsEnum::EDIT_ALL_TICKETS->value)) {
            return true;
        }

        if ($user->hasPermissionTo(PermissionsEnum::EDIT_ASSIGNEE_TICKETS->value) && $ticket->assignee_id == $user->id) {
            return true;
        }

        return $ticket->author_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Ticket $ticket
     * @return Response|bool
     */
    public function delete(User $user, Ticket $ticket)
    {
        if ($user->hasPermissionTo(PermissionsEnum::DELETE_ALL_TICKETS->value)) {
            return true;
        }

        return $user->can(PermissionsEnum::DELETE_OWN_TICKETS->value) && $ticket->author_id == $user->id;
    }
}

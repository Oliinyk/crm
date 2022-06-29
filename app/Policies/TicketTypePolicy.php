<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TicketTypePolicy
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
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param TicketType $ticketType
     * @return Response|bool
     */
    public function view(User $user, TicketType $ticketType)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param TicketType $ticketType
     * @return Response|bool
     */
    public function update(User $user, TicketType $ticketType)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @return Response|bool
     */
    public function delete(User $user)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * Handle the TicketType "restored" event.
     *
     * @param User $user
     * @param TicketType $ticketType
     * @return bool
     */
    public function restored(User $user, TicketType $ticketType)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * Handle the TicketType "force deleted" event.
     *
     * @param User $user
     * @param TicketType $ticketType
     * @return bool
     */
    public function forceDeleted(User $user, TicketType $ticketType)
    {
        return $user->hasPermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }
}

<?php

namespace App\Http\Resources;

use App\Enums\PermissionsEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $allProjects = $this->hasPermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $joinedProjects = $this->hasPermissionTo(PermissionsEnum::SEE_JOINED_PROJECTS->value);

        $deleteAllProjects = $this->hasPermissionTo(PermissionsEnum::DELETE_ALL_PROJECTS->value);
        $deleteOwnProjects = $this->hasPermissionTo(PermissionsEnum::DELETE_OWN_PROJECTS->value);

        $seeAllTickets = $this->hasPermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);
        $seeJoinedTickets = $this->hasPermissionTo(PermissionsEnum::SEE_JOINED_TICKETS->value);

        $editAllTickets = $this->hasPermissionTo(PermissionsEnum::EDIT_ALL_TICKETS->value);
        $editAssigneeTickets = $this->hasPermissionTo(PermissionsEnum::EDIT_ASSIGNEE_TICKETS->value);

        $deleteAllTickets = $this->hasPermissionTo(PermissionsEnum::DELETE_ALL_TICKETS->value);
        $deleteOwnTickets = $this->hasPermissionTo(PermissionsEnum::DELETE_OWN_TICKETS->value);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'users_count' => $this->users_count,
            'see_projects' => $allProjects ? PermissionsEnum::SEE_ALL_PROJECTS->value : ($joinedProjects ? PermissionsEnum::SEE_JOINED_PROJECTS->value : null),
            'create_projects' => $this->hasPermissionTo(PermissionsEnum::CREATE_PROJECTS->value) ? PermissionsEnum::CREATE_PROJECTS->value : null,
            'edit_all_projects' => $this->hasPermissionTo(PermissionsEnum::EDIT_ALL_PROJECTS->value) ? PermissionsEnum::EDIT_ALL_PROJECTS->value : null,
            'delete_projects' => $deleteAllProjects ? PermissionsEnum::DELETE_ALL_PROJECTS->value : ($deleteOwnProjects ? PermissionsEnum::DELETE_OWN_PROJECTS->value : null),
            'manage_groups' => $this->hasPermissionTo(PermissionsEnum::MANAGE_GROUPS->value) ? PermissionsEnum::MANAGE_GROUPS->value : null,
            'see_clients' => $this->hasPermissionTo(PermissionsEnum::SEE_CLIENTS->value) ? PermissionsEnum::SEE_CLIENTS->value : null,
            'add_clients' => $this->hasPermissionTo(PermissionsEnum::ADD_CLIENTS->value) ? PermissionsEnum::ADD_CLIENTS->value : null,
            'delete_clients' => $this->hasPermissionTo(PermissionsEnum::DELETE_CLIENTS->value) ? PermissionsEnum::DELETE_CLIENTS->value : null,
            'see_roles' => $this->hasPermissionTo(PermissionsEnum::SEE_ROLES->value) ? PermissionsEnum::SEE_ROLES->value : null,
            'add_roles' => $this->hasPermissionTo(PermissionsEnum::ADD_ROLES->value) ? PermissionsEnum::ADD_ROLES->value : null,
            'delete_roles' => $this->hasPermissionTo(PermissionsEnum::DELETE_ROLES->value) ? PermissionsEnum::DELETE_ROLES->value : null,
            'manage_ticket_types' => $this->hasPermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value) ? PermissionsEnum::MANAGE_TICKET_TYPES->value : null,
            'see_tickets' => $seeAllTickets ? PermissionsEnum::SEE_ALL_TICKETS->value : ($seeJoinedTickets ? PermissionsEnum::SEE_JOINED_TICKETS->value : null),
            'create_tickets' => $this->hasPermissionTo(PermissionsEnum::CREATE_TICKETS->value) ? PermissionsEnum::CREATE_TICKETS->value : null,
            'edit_all_tickets' => $editAllTickets ? PermissionsEnum::EDIT_ALL_TICKETS->value : ($editAssigneeTickets ? PermissionsEnum::EDIT_ASSIGNEE_TICKETS->value : null),
            'delete_tickets' => $deleteAllTickets ? PermissionsEnum::DELETE_ALL_TICKETS->value : ($deleteOwnTickets ? PermissionsEnum::DELETE_OWN_TICKETS->value : null),
            'manage_project_members' => $this->hasPermissionTo(PermissionsEnum::MANAGE_PROJECT_MEMBERS->value) ? PermissionsEnum::MANAGE_PROJECT_MEMBERS->value : null,
        ];
    }
}

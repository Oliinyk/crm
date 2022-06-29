<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum PermissionsEnum: string
{
    use Values;

    case CREATE_PROJECTS = 'create projects';
    case SEE_ALL_PROJECTS = 'see all projects';
    case SEE_JOINED_PROJECTS = 'see joined projects';
    case EDIT_ALL_PROJECTS = 'edit all projects';
    case DELETE_ALL_PROJECTS = 'delete all projects';
    case DELETE_OWN_PROJECTS = 'delete own projects';
    case MANAGE_PROJECT_MEMBERS = 'manage project members';
    case MANAGE_GROUPS = 'manage groups';
    case SEE_CLIENTS = 'see clients';
    case ADD_CLIENTS = 'add clients';
    case DELETE_CLIENTS = 'delete clients';
    case SEE_ROLES = 'see roles';
    case ADD_ROLES = 'add roles';
    case DELETE_ROLES = 'delete roles';
    case MANAGE_TICKET_TYPES = 'manage ticket types';
    case CREATE_TICKETS = 'create tickets';
    case SEE_ALL_TICKETS = 'see all tickets';
    case SEE_JOINED_TICKETS = 'see joined tickets';
    case EDIT_ALL_TICKETS = 'edit all tickets';
    case EDIT_ASSIGNEE_TICKETS = 'edit assignee tickets';
    case DELETE_ALL_TICKETS = 'delete all tickets';
    case DELETE_OWN_TICKETS = 'delete own tickets';
    case CREATE_LAYERS = 'create layers';
    case EDIT_ALL_LAYERS = 'edit all layers';
    case DELETE_ALL_LAYERS = 'delete all layers';
    case DELETE_OWN_LAYERS = 'delete own layers';
    case MANAGE_INVITATIONS = 'manage invitations';
}
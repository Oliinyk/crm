<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use ReflectionClass;
use Spatie\Permission\Models\Role;

/**
 * App\Models\Permission
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection|Role[] $roles
 * @property-read int|null $roles_count
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @method static Builder|Permission newModelQuery()
 * @method static Builder|Permission newQuery()
 * @method static Builder|Permission permission($permissions)
 * @method static Builder|Permission query()
 * @method static Builder|Permission role($roles, $guard = null)
 * @method static Builder|Permission whereCreatedAt($value)
 * @method static Builder|Permission whereGuardName($value)
 * @method static Builder|Permission whereId($value)
 * @method static Builder|Permission whereName($value)
 * @method static Builder|Permission whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Permission extends \Spatie\Permission\Models\Permission
{
    const CREATE_PROJECTS = 'create projects';

    const SEE_ALL_PROJECTS = 'see all projects';

    const SEE_JOINED_PROJECTS = 'see joined projects';

    const EDIT_ALL_PROJECTS = 'edit all projects';

    const DELETE_ALL_PROJECTS = 'delete all projects';

    const DELETE_OWN_PROJECTS = 'delete own projects';

    const MANAGE_PROJECT_MEMBERS = 'manage project members';

    const MANAGE_GROUPS = 'manage groups';

    const SEE_CLIENTS = 'see clients';

    const ADD_CLIENTS = 'add clients';

    const DELETE_CLIENTS = 'delete clients';

    const SEE_ROLES = 'see roles';

    const ADD_ROLES = 'add roles';

    const DELETE_ROLES = 'delete roles';

    const MANAGE_TICKET_TYPES = 'manage ticket types';

    const CREATE_TICKETS = 'create tickets';

    const SEE_ALL_TICKETS = 'see all tickets';

    const SEE_JOINED_TICKETS = 'see joined tickets';

    const EDIT_ALL_TICKETS = 'edit all tickets';

    const EDIT_ASSIGNEE_TICKETS = 'edit assignee tickets';

    const DELETE_ALL_TICKETS = 'delete all tickets';

    const DELETE_OWN_TICKETS = 'delete own tickets';

    const CREATE_LAYERS = 'create layers';

    const EDIT_ALL_LAYERS = 'edit all layers';

    const DELETE_ALL_LAYERS = 'delete all layers';

    const DELETE_OWN_LAYERS = 'delete own layers';

    const MANAGE_INVITATIONS = 'manage invitations';

    /**
     * Get all constant variables of this class as permission names.
     *
     * @return array
     */
    public static function getAllPermissionNames()
    {
        return array_values((new ReflectionClass(__CLASS__))->getConstants());
    }
}

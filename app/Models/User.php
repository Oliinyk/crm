<?php

namespace App\Models;

use App\Scopes\WorkspaceScope;
use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $full_name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property int|null $workspace_id
 * @property string $locale
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection|Group[] $groups
 * @property-read int|null $groups_count
 * @property-read Collection|Invitation[] $invitations
 * @property-read int|null $invitations_count
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|Project[] $ownProjects
 * @property-read int|null $own_projects_count
 * @property-read Collection|Workspace[] $ownWorkspaces
 * @property-read int|null $own_workspaces_count
 * @property-read Collection|Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection|Project[] $projects
 * @property-read int|null $projects_count
 * @property-read Collection|Role[] $roles
 * @property-read int|null $roles_count
 * @property-read Collection|Workspace[] $workspaces
 * @property-read int|null $workspaces_count
 * @method static UserFactory factory(...$parameters)
 * @method static Builder|User filter(array $filters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static Builder|User orderByName()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User whereLocale($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereWorkspaceId($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @mixin Eloquent
 * @property-read Collection|Ticket[] $watchedTickets
 * @property-read int|null $watched_tickets_count
 * @method static Builder|User memberOfTheWorkspace()
 * @property string $first_name
 * @property string $last_name
 * @method static Builder|User whereFullName($value)
 */
class User extends Authenticatable implements HasMedia
{
    use HasFactory,
        Notifiable,
        SoftDeletes,
        InteractsWithMedia,
        HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'workspace_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['media'];

    /**
     * @param $query
     */
    public function scopeOrderByName($query)
    {
        $query->orderBy('full_name');
    }

    /**
     * @param $query
     * @param array $filters
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('full_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        })->when($filters['project_id'] ?? null, function (Builder $query, $id) {
            $query->whereHas('projects', function ($query) use ($id) {
                $query->where('id', $id);
            });
        });
    }

    /**
     * @param $query
     * @param Workspace $workspace
     */
    public function scopeMemberOfTheWorkspace($query, Workspace $workspace)
    {
        $query->whereHas('workspaces', fn ($query) => $query->whereId($workspace->id));
    }

    /**
     * Workspaces owned by the User.
     *
     * @return HasMany
     */
    public function ownWorkspaces()
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    /**
     * Workspaces in which the user is a member.
     *
     * @return MorphToMany
     */
    public function workspaces()
    {
        return $this->morphedByMany(Workspace::class, 'member');
    }

    /**
     * Projects owned by the User.
     *
     * @return HasMany
     */
    public function ownProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    /**
     * Projects in which the user is a member.
     *
     * @return MorphToMany
     */
    public function projects()
    {
        return $this->morphedByMany(Project::class, 'member');
    }

    /**
     * Groups in which the user is a member.
     *
     * @return MorphToMany
     */
    public function groups()
    {
        return $this->morphedByMany(Group::class, 'member');
    }

    /**
     * @return HasMany
     */
    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'email', 'email')
            ->withoutGlobalScope(WorkspaceScope::class);
    }

    /**
     * Groups in which the user is a member.
     *
     * @return MorphToMany
     */
    public function watchedTickets()
    {
        return $this->morphedByMany(Ticket::class, 'member');
    }
}

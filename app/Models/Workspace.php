<?php

namespace App\Models;

use Database\Factories\WorkspaceFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Workspace
 *
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string $plan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Invitation[] $invitations
 * @property-read int|null $invitations_count
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property-read Collection|User[] $members
 * @property-read int|null $members_count
 * @property-read User $owner
 * @property-read Collection|Project[] $projects
 * @property-read int|null $projects_count
 * @property-read Collection|TicketType[] $ticketTypes
 * @property-read int|null $ticket_types_count
 * @method static WorkspaceFactory factory(...$parameters)
 * @method static Builder|Workspace newModelQuery()
 * @method static Builder|Workspace newQuery()
 * @method static Builder|Workspace query()
 * @method static Builder|Workspace whereCreatedAt($value)
 * @method static Builder|Workspace whereId($value)
 * @method static Builder|Workspace whereName($value)
 * @method static Builder|Workspace whereOwnerId($value)
 * @method static Builder|Workspace wherePlan($value)
 * @method static Builder|Workspace whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Workspace extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'plan',
        'owner_id',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['media'];

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @param string|null $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->whereHas('members', function (Builder $query) {
                $query->where('user_id', request()->user()->id);
            })
            ->firstOrFail();
    }

    /**
     * The Workspace owner.
     *
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The Workspace Members.
     *
     * @return MorphToMany
     */
    public function members()
    {
        return $this->morphToMany(User::class, 'member', 'members');
    }

    /**
     * The Workspace projects.
     *
     * @return HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * The Workspace ticket types.
     *
     * @return HasMany
     */
    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    /**
     * Users invited to the group.
     *
     * @return HasMany
     */
    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
}

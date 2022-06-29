<?php

namespace App\Models;

use App\Traits\BelongsToWorkspace;
use Database\Factories\ProjectFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Project
 *
 * @property int $id
 * @property int $workspace_id
 * @property int $owner_id
 * @property int|null $client_id
 * @property string $name
 * @property int $working_hours
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection|Layer[] $layers
 * @property-read int|null $layers_count
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property-read Collection|User[] $members
 * @property-read int|null $members_count
 * @property-read User $owner
 * @property-read Workspace $workspace
 * @method static ProjectFactory factory(...$parameters)
 * @method static Builder|Project filter(array $filters)
 * @method static Builder|Project newModelQuery()
 * @method static Builder|Project newQuery()
 * @method static \Illuminate\Database\Query\Builder|Project onlyTrashed()
 * @method static Builder|Project query()
 * @method static Builder|Project whereClientId($value)
 * @method static Builder|Project whereCreatedAt($value)
 * @method static Builder|Project whereDeletedAt($value)
 * @method static Builder|Project whereId($value)
 * @method static Builder|Project whereName($value)
 * @method static Builder|Project whereOwnerId($value)
 * @method static Builder|Project whereUpdatedAt($value)
 * @method static Builder|Project whereWorkingHours($value)
 * @method static Builder|Project whereWorkspaceId($value)
 * @method static \Illuminate\Database\Query\Builder|Project withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Project withoutTrashed()
 * @mixin Eloquent
 */
class Project extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, BelongsToWorkspace;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'workspace_id',
        'owner_id',
        'client_id',
        'name',
        'working_hours',
    ];

    /**
     * @param $query
     * @param array $filters
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%'.$search.'%');
        });
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @param string|null $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->withTrashed()->firstOrFail();
    }

    /**
     * The Project owner.
     *
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The Project Members.
     *
     * @return MorphToMany
     */
    public function members()
    {
        return $this->morphToMany(User::class, 'member', 'members');
    }

    /**
     * The Project Layers.
     *
     * @return HasMany
     */
    public function layers()
    {
        return $this->hasMany(Layer::class);
    }
}

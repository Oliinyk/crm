<?php

namespace App\Models;

use App\Traits\BelongsToWorkspace;
use Database\Factories\LayerFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Layer
 *
 * @property int $id
 * @property string $title
 * @property int $workspace_id
 * @property int $project_id
 * @property int $author_id
 * @property int|null $parent_layer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $author
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property-read Layer|null $parentLayer
 * @property-read Project $project
 * @property-read Workspace $workspace
 * @method static LayerFactory factory(...$parameters)
 * @method static Builder|Layer filter(array $filters)
 * @method static Builder|Layer newModelQuery()
 * @method static Builder|Layer newQuery()
 * @method static Builder|Layer query()
 * @method static Builder|Layer whereAuthorId($value)
 * @method static Builder|Layer whereCreatedAt($value)
 * @method static Builder|Layer whereId($value)
 * @method static Builder|Layer whereParentLayerId($value)
 * @method static Builder|Layer whereProjectId($value)
 * @method static Builder|Layer whereTitle($value)
 * @method static Builder|Layer whereUpdatedAt($value)
 * @method static Builder|Layer whereWorkspaceId($value)
 * @mixin Eloquent
 */
class Layer extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, BelongsToWorkspace;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'workspace_id',
        'project_id',
        'author_id',
        'parent_layer_id',
    ];

    /**
     * @param $query
     * @param array $filters
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%');
            })->orWhereHas('media', function (Builder $query) use ($search) {
                $query->where('file_name', 'like', '%'.$search.'%');
            });
        });
    }

    /**
     * Get the ticket's project.
     *
     * @return BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the ticket's author.
     *
     * @return BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the layer's parent.
     *
     * @return BelongsTo
     */
    public function parentLayer()
    {
        return $this->belongsTo(self::class);
    }
}

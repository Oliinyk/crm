<?php

namespace App\Models;

use App\Traits\BelongsToWorkspace;
use Database\Factories\GroupFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Group
 *
 * @property int $id
 * @property string $name
 * @property int $workspace_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|User[] $members
 * @property-read int|null $members_count
 * @property-read Workspace $workspace
 * @method static GroupFactory factory(...$parameters)
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group query()
 * @method static Builder|Group whereCreatedAt($value)
 * @method static Builder|Group whereId($value)
 * @method static Builder|Group whereName($value)
 * @method static Builder|Group whereUpdatedAt($value)
 * @method static Builder|Group whereWorkspaceId($value)
 * @mixin Eloquent
 */
class Group extends Model
{
    use HasFactory, BelongsToWorkspace;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'workspace_id',
    ];

    /**
     * The Project Members.
     *
     * @return MorphToMany
     */
    public function members()
    {
        return $this->morphToMany(User::class, 'member', 'members');
    }
}

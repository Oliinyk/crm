<?php

namespace App\Models;

use Database\Factories\InvitationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Invitation
 *
 * @property int $id
 * @property string $email
 * @property string $token
 * @property int $workspace_id
 * @property int $author_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $author
 * @property-read Workspace $workspace
 * @method static InvitationFactory factory(...$parameters)
 * @method static Builder|Invitation newModelQuery()
 * @method static Builder|Invitation newQuery()
 * @method static Builder|Invitation query()
 * @method static Builder|Invitation whereAuthorId($value)
 * @method static Builder|Invitation whereCreatedAt($value)
 * @method static Builder|Invitation whereEmail($value)
 * @method static Builder|Invitation whereId($value)
 * @method static Builder|Invitation whereToken($value)
 * @method static Builder|Invitation whereUpdatedAt($value)
 * @method static Builder|Invitation whereWorkspaceId($value)
 * @mixin Eloquent
 */
class Invitation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'author_id',
        'email',
        'token',
        'workspace_id',
    ];

    /**
     * The invitation's author.
     *
     * @return BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The Model's Workspace.
     *
     * @return BelongsTo
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}

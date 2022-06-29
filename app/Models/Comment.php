<?php

namespace App\Models;

use App\Traits\BelongsToWorkspace;
use Database\Factories\CommentFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Comment
 *
 * @property-read User|null $author
 * @property-read Ticket|null $ticket
 * @property-read Workspace|null $workspace
 * @method static CommentFactory factory(...$parameters)
 * @method static Builder|Comment newModelQuery()
 * @method static Builder|Comment newQuery()
 * @method static Builder|Comment query()
 * @mixin Eloquent
 * @property int $id
 * @property int $workspace_id
 * @property int $author_id
 * @property int $ticket_id
 * @property string $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Comment whereAuthorId($value)
 * @method static Builder|Comment whereCreatedAt($value)
 * @method static Builder|Comment whereId($value)
 * @method static Builder|Comment whereText($value)
 * @method static Builder|Comment whereTicketId($value)
 * @method static Builder|Comment whereUpdatedAt($value)
 * @method static Builder|Comment whereWorkspaceId($value)
 */
class Comment extends Model
{
    use HasFactory, BelongsToWorkspace;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'text',
        'workspace_id',
        'author_id',
        'ticket_id',
    ];

    /**
     * The comment's ticket.
     *
     * @return BelongsTo
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * The invitation's author.
     *
     * @return BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }
}

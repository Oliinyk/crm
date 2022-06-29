<?php

namespace App\Models;

use App\Traits\BelongsToWorkspace;
use Carbon\CarbonInterval;
use Database\Factories\TimeEntryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\TimeEntry
 *
 * @property int $id
 * @property int $workspace_id
 * @property int $ticket_id
 * @property int $author_id
 * @property string|null $description
 * @property CarbonInterval $time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $author
 * @property-read Ticket|null $ticket
 * @property-read Workspace|null $workspace
 * @method static TimeEntryFactory factory(...$parameters)
 * @method static Builder|TimeEntry newModelQuery()
 * @method static Builder|TimeEntry newQuery()
 * @method static Builder|TimeEntry query()
 * @method static Builder|TimeEntry whereAuthorId($value)
 * @method static Builder|TimeEntry whereCreatedAt($value)
 * @method static Builder|TimeEntry whereDescription($value)
 * @method static Builder|TimeEntry whereId($value)
 * @method static Builder|TimeEntry whereTicketId($value)
 * @method static Builder|TimeEntry whereTime($value)
 * @method static Builder|TimeEntry whereUpdatedAt($value)
 * @method static Builder|TimeEntry whereWorkspaceId($value)
 * @mixin Eloquent
 * @property string $type
 * @method static Builder|TimeEntry whereType($value)
 */
class TimeEntry extends Model
{
    use HasFactory,
        BelongsToWorkspace;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable=[
        'workspace_id',
        'author_id',
        'ticket_id',
        'description',
        'time',
        'created_at',
        'type'
    ];

    /**
     * Get the ticket's time estimate.
     */
    protected function time(): Attribute
    {
        return new Attribute(
            get: fn ($value) => CarbonInterval::minutes($this->attributes['time'])?->cascade(),
            set: fn ($value) =>  CarbonInterval::make($value)?->totalMinutes,
        );
    }

    /**
     * The time entry's author.
     *
     * @return BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The time entry's ticket.
     *
     * @return BelongsTo
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}

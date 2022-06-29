<?php

namespace App\Models;

use App\Traits\BelongsToWorkspace;
use Carbon\CarbonInterval;
use Database\Factories\TicketFactory;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Ticket
 *
 * @property int $id
 * @property int $workspace_id
 * @property int $ticket_type_id
 * @property int $project_id
 * @property int $author_id
 * @property int|null $parent_ticket_id
 * @property int|null $assignee_id
 * @property int|null $layer_id
 * @property string $title
 * @property string|null $status
 * @property string|null $priority
 * @property int|null $progress
 * @property string|null $watchers
 * @property mixed|null $start_date
 * @property mixed|null $due_date
 * @property int|null $time_estimate
 * @property int|null $time_spent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $assignee
 * @property-read User $author
 * @property-read Ticket|null $parentTicket
 * @property-read Project $project
 * @property-read Collection|TicketField[] $ticketFields
 * @property-read int|null $ticket_fields_count
 * @property-read TicketType $ticketType
 * @property-read Workspace $workspace
 * @method static TicketFactory factory(...$parameters)
 * @method static Builder|Ticket filter(array $filters)
 * @method static Builder|Ticket newModelQuery()
 * @method static Builder|Ticket newQuery()
 * @method static Builder|Ticket query()
 * @method static Builder|Ticket whereAssigneeId($value)
 * @method static Builder|Ticket whereAuthorId($value)
 * @method static Builder|Ticket whereCreatedAt($value)
 * @method static Builder|Ticket whereDueDate($value)
 * @method static Builder|Ticket whereId($value)
 * @method static Builder|Ticket whereLayerId($value)
 * @method static Builder|Ticket whereParentTicketId($value)
 * @method static Builder|Ticket wherePriority($value)
 * @method static Builder|Ticket whereProgress($value)
 * @method static Builder|Ticket whereProjectId($value)
 * @method static Builder|Ticket whereStartDate($value)
 * @method static Builder|Ticket whereStatus($value)
 * @method static Builder|Ticket whereTicketTypeId($value)
 * @method static Builder|Ticket whereTimeEstimate($value)
 * @method static Builder|Ticket whereTimeSpent($value)
 * @method static Builder|Ticket whereTitle($value)
 * @method static Builder|Ticket whereUpdatedAt($value)
 * @method static Builder|Ticket whereWatchers($value)
 * @method static Builder|Ticket whereWorkspaceId($value)
 * @mixin Eloquent
 * @property-read int|null $watchers_count
 * @property-read Layer|null $layer
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property-read Collection|Ticket[] $childTickets
 * @property-read int|null $child_tickets_count
 * @property-read Collection|Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read int $time_percent
 * @property-read int|string|null $time_remaining
 * @property-read Collection|TimeEntry[] $timeEntries
 * @property-read int|null $time_entries_count
 */
class Ticket extends Model implements HasMedia
{
    use HasFactory,
        BelongsToWorkspace,
        InteractsWithMedia,
        LogsActivity;

    const STATUS_OPEN = 'open';

    const STATUS_RESOLVED = 'resolved';

    const STATUS_IN_PROGRESS = 'in_progress';

    const PRIORITY_LOW = 'low';

    const PRIORITY_MEDIUM = 'medium';

    const PRIORITY_HIGH = 'high';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'workspace_id',
        'ticket_type_id',
        'project_id',
        'author_id',
        'parent_ticket_id',
        'assignee_id',
        'layer_id',
        'title',
        'status',
        'priority',
        'progress',
        'watchers',
        'start_date',
        'due_date',
        'time_estimate',
        'time_spent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    protected static $recordEvents = ['created'];

    /**
     * Get the ticket's time estimate.
     */
    protected function timeEstimate(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getCarbonInterval($this->attributes['time_estimate']),
            set: fn ($value) => $value == 0 ? 0 : CarbonInterval::make($value)?->totalMinutes,
        );
    }

    /**
     * Get the ticket's time estimate.
     */
    protected function timeSpent(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getCarbonInterval($this->attributes['time_spent']),
            set: fn ($value) => $value == 0 ? 0 : CarbonInterval::make($value)?->totalMinutes,
        );
    }

    /**
     * Get Time Remaining attribute
     *
     * @return int|string|null
     * @throws Exception
     */
    public function getTimeRemainingAttribute()
    {
        $result = $this->attributes['time_estimate'] - $this->attributes['time_spent'];

        if ($result < 0 || is_null($this->attributes['time_spent'])) {
            return null;
        }

        if ($result == 0) {
            return 0;
        }

        return $this->getCarbonInterval($result);
    }

    /**
     * Get Time percent attribute
     *
     * @return int
     */
    public function getTimePercentAttribute()
    {
        $estimate = $this->attributes['time_estimate'] / 100;

        if (! $estimate) {
            return 0;
        }
        $result = intval($this->attributes['time_spent'] / $estimate);

        if ($result > 100) {
            return 101;
        }

        return $result;
    }

    /**
     * Get Carbon interval
     *
     * @param $data
     * @return string|null
     * @throws Exception
     */
    public function getCarbonInterval($data)
    {
        if (! $data) {
            return null;
        }

        return CarbonInterval::minutes($data)->cascade()->forHumans([
            'skip' => ['week'],
            'minimumUnit' => 'minute',
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly([]);
    }

    /**
     * @param $query
     * @param array $filters
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {

            $query->where(function (Builder $query) use ($search) {

                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhereHas('ticketFields', function (Builder $query) use ($search) {

                        $query->where([
                            ['type', '=', TicketField::TYPE_SHORT_TEXT],
                            ['string_value', 'like', '%'.$search.'%'],
                        ])->orWhere([
                            ['type', '=', TicketField::TYPE_LONG_TEXT],
                            ['text_value', 'like', '%'.$search.'%'],
                        ])->orWhere([
                            ['type', '=', TicketField::TYPE_NUMERAL],
                            ['int_value', 'like', '%'.$search.'%'],
                        ])->orWhere([
                            ['type', '=', TicketField::TYPE_DECIMAL],
                            ['decimal_value', 'like', '%'.$search.'%'],
                        ])->orWhere([
                            ['type', '=', TicketField::TYPE_DATE],
                            ['date_value', 'like', '%'.$search.'%'],
                        ])->orWhere([
                            ['type', '=', TicketField::TYPE_TIME],
                            ['time_value', 'like', '%'.$search.'%'],
                        ]);
                        
                    });

            });

        })->when($filters['author_id'] ?? null, function (Builder $query, $author_id) {

            $query->where('author_id', '=', $author_id);

        })->when($filters['status'] ?? null, function (Builder $query, $status) {

            $query->where('status', '=', $status);

        })->when($filters['priority'] ?? null, function (Builder $query, $priority) {

            $query->where('priority', '=', $priority);

        })->when($filters['parent_ticket'] ?? null, function (Builder $query, $parent_tickets) {

            $query->whereIn('parent_ticket_id', $parent_tickets);

        })->when($filters['assignee'] ?? null, function (Builder $query, $assignees) {

            $assignees = collect($assignees)->pluck('id');

            $query->whereIn('assignee_id', $assignees);

        })->when($filters['layer'] ?? null, function (Builder $query, $layers) {

            $layers = collect($layers)->pluck('id');

            $query->whereIn('layer_id', $layers);

        })->when($filters['watchers'] ?? null, function (Builder $query, $watchers) {

            $query->whereHas('watchers', function (Builder $query) use ($watchers) {

                $watchers = collect($watchers)->pluck('id');

                $query->whereIn('id', $watchers);

            });

        })->when($filters['types'] ?? null, function (Builder $query, $types) {

            $types = collect($types)->pluck('id');

            $query->whereIn('ticket_type_id', $types);

        })->when($filters['start_date'] ?? null, function (Builder $query, $dates) {

            $from = Carbon::parse($dates[0])->startOfDay()->toDateString();
            $to = Carbon::parse($dates[1])->endOfDay()->toDateString();

            $query->whereDate('start_date', '>=', $from)
                ->whereDate('start_date', '<=', $to);

        })->when($filters['due_date'] ?? null, function (Builder $query, $dates) {

            $from = Carbon::parse($dates[0])->startOfDay()->toDateString();
            $to = Carbon::parse($dates[1])->endOfDay()->toDateString();

            $query->whereDate('due_date', '>=', $from)
                ->whereDate('due_date', '<=', $to);

        })->when($filters['progress'] ?? null, function (Builder $query, $progress) {
            $query->whereBetween('progress', [$progress['start'], $progress['end']]);
        })->when($filters['changed'] ?? null, function (Builder $query, $dates) {

            $from = Carbon::parse($dates[0])->startOfDay()->toDateString();
            $to = Carbon::parse($dates[1])->endOfDay()->toDateString();

            $query->whereDate('updated_at', '>=', $from)
                ->whereDate('updated_at', '<=', $to);

        });
    }

    /**
     * The Ticket type.
     *
     * @return BelongsTo
     */
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    /**
     * Get all the ticket type's custom fields.
     *
     * @return MorphMany
     */
    public function ticketFields()
    {
        return $this->morphMany(TicketField::class, 'ticket_field')->orderBy('order');
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
     * Get the ticket's parent.
     *
     * @return BelongsTo
     */
    public function parentTicket()
    {
        return $this->belongsTo(self::class);
    }

    /**
     * Get the ticket's children.
     *
     * @return HasMany
     */
    public function childTickets()
    {
        return $this->hasMany(self::class, 'parent_ticket_id');
    }

    /**
     * Get the ticket's assignee.
     *
     * @return BelongsTo
     */
    public function assignee()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The Ticket Watchers.
     *
     * @return MorphToMany
     */
    public function watchers()
    {
        return $this->morphToMany(User::class, 'member', 'members');
    }

    /**
     * The Ticket Layer.
     *
     * @return BelongsTo
     */
    public function layer()
    {
        return $this->belongsTo(Layer::class);
    }

    /**
     * @return HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderByDesc('created_at');
    }

    /**
     * @return HasMany
     */
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }
}

<?php

namespace App\Models;

use App\Traits\BelongsToWorkspace;
use Database\Factories\TicketFieldFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\CustomField
 *
 * @property int $id
 * @property int $workspace_id
 * @property string $type
 * @property string $name
 * @property int $order
 * @property string $ticket_field_type
 * @property int $ticket_field_id
 * @property string|null $value
 * @property string|null $date_value
 * @property string|null $time_value
 * @property string|null $string_value
 * @property string|null $text_value
 * @property int|null $int_value
 * @property string|null $decimal_value
 * @property int|null $boolean_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|Eloquent $ticketField
 * @property-read Workspace $workspace
 * @method static TicketFieldFactory factory(...$parameters)
 * @method static Builder|TicketField newModelQuery()
 * @method static Builder|TicketField newQuery()
 * @method static Builder|TicketField query()
 * @method static Builder|TicketField whereBooleanValue($value)
 * @method static Builder|TicketField whereCreatedAt($value)
 * @method static Builder|TicketField whereDateValue($value)
 * @method static Builder|TicketField whereDecimalValue($value)
 * @method static Builder|TicketField whereId($value)
 * @method static Builder|TicketField whereIntValue($value)
 * @method static Builder|TicketField whereName($value)
 * @method static Builder|TicketField whereOrder($value)
 * @method static Builder|TicketField whereStringValue($value)
 * @method static Builder|TicketField whereTextValue($value)
 * @method static Builder|TicketField whereTicketFieldId($value)
 * @method static Builder|TicketField whereTicketFieldType($value)
 * @method static Builder|TicketField whereTimeValue($value)
 * @method static Builder|TicketField whereType($value)
 * @method static Builder|TicketField whereUpdatedAt($value)
 * @method static Builder|TicketField whereValue($value)
 * @method static Builder|TicketField whereWorkspaceId($value)
 * @mixin Eloquent
 */
class TicketField extends Model
{
    use HasFactory, BelongsToWorkspace;

    const TYPE_STATUS = 'status';

    const TYPE_PRIORITY = 'priority';

    const TYPE_LAYER = 'layer';

    const TYPE_PARENT_TICKET = 'parent_ticket';

    const TYPE_CHILD_TICKET = 'child_ticket';

    const TYPE_PROGRESS = 'progress';

    const TYPE_ASSIGNEE = 'assignee';

    const TYPE_WATCHERS = 'watchers';

    const TYPE_SEPARATOR = 'separator';

    const TYPE_START_DATE = 'start_date';

    const TYPE_DUE_DATE = 'due_date';

    const TYPE_ESTIMATE = 'time_estimate';

    const TYPE_TIME_SPENT = 'time_spent';

    const TYPE_DATE = 'date';

    const TYPE_TIME = 'time';

    const TYPE_SHORT_TEXT = 'short_text';

    const TYPE_LONG_TEXT = 'long_text';

    const TYPE_NUMERAL = 'numeral';

    const TYPE_DECIMAL = 'decimal';

    const TYPE_CHECKBOX = 'checkbox';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'workspace_id',
        'type',
        'name',
        'order',
        'value',
        'date_value',
        'time_value',
        'string_value',
        'text_value',
        'int_value',
        'decimal_value',
        'boolean_value',
        'ticket_field_id',
        'ticket_field_type',
    ];

    /**
     * Get all custom types.
     *
     * @return string[]
     */
    public static function customTypes()
    {
        return [
            self::TYPE_DATE,
            self::TYPE_TIME,
            self::TYPE_SHORT_TEXT,
            self::TYPE_LONG_TEXT,
            self::TYPE_NUMERAL,
            self::TYPE_DECIMAL,
            self::TYPE_CHECKBOX,
            self::TYPE_SEPARATOR,
        ];
    }

    /**
     * Get all default types.
     *
     * @return string[]
     */
    public static function defaultTypes()
    {
        return [
            self::TYPE_STATUS,
            self::TYPE_PRIORITY,
            self::TYPE_LAYER,
            self::TYPE_PARENT_TICKET,
            self::TYPE_PROGRESS,
            self::TYPE_ASSIGNEE,
            self::TYPE_WATCHERS,
            self::TYPE_START_DATE,
            self::TYPE_DUE_DATE,
            self::TYPE_ESTIMATE,
            self::TYPE_TIME_SPENT,
        ];
    }

    /**
     * Get all ticket types.
     *
     * @return array
     */
    public static function ticketTypes()
    {
        return collect([self::defaultTypes(), self::customTypes()])
            ->flatten(1)
            ->toArray();
    }

    /**
     * Get the parent commentable model (TicketType or Ticket).
     */
    public function ticketField()
    {
        return $this->morphTo();
    }

    public static function tableFields($id)
    {
        $currentFields = collect(Cache::get("user.{$id}.tickets.table"), []);

        $fields = self::whereTicketFieldType(TicketType::class)
            ->whereIn('type', self::defaultTypes())
            ->get('type')
            ->pluck('type')
            ->unique()
            ->prepend('changed')
            ->prepend('ticket_type')
            ->prepend('title')
            ->prepend('id');

        return $currentFields
            ->pluck('name')
            ->intersect($fields)
            ->merge($fields->diff($currentFields->pluck('name')))
            ->filter(fn ($data) => $data != self::TYPE_PARENT_TICKET)
            ->transform(function ($type, $key) use ($currentFields) {
                $oldType = $currentFields->firstWhere('name', '=', $type);

                return [
                    'name' => $type,
                    'id' => $key + 1,
                    'show' => is_array($oldType) ? $oldType['show'] : ($type == 'id' || $type == 'title'),
                    'disabledMarked' => $type == 'id' || $type == 'title',
                ];
            })
            ->values();
    }
}

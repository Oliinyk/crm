<?php

namespace App\Models;

use App\Traits\BelongsToWorkspace;
use Database\Factories\TicketTypeFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * App\Models\TicketType
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property int $workspace_id
 * @property int $author_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $author
 * @property-read Collection|TicketField[] $customTicketFields
 * @property-read int|null $custom_ticket_fields_count
 * @property-read Collection|TicketField[] $defaultTicketFields
 * @property-read int|null $default_ticket_fields_count
 * @property-read Collection|TicketField[] $ticketFields
 * @property-read int|null $ticket_fields_count
 * @property-read Collection|Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @property-read Workspace $workspace
 * @method static TicketTypeFactory factory(...$parameters)
 * @method static Builder|TicketType filter(array $filters)
 * @method static Builder|TicketType newModelQuery()
 * @method static Builder|TicketType newQuery()
 * @method static \Illuminate\Database\Query\Builder|TicketType onlyTrashed()
 * @method static Builder|TicketType query()
 * @method static Builder|TicketType whereAuthorId($value)
 * @method static Builder|TicketType whereCreatedAt($value)
 * @method static Builder|TicketType whereDeletedAt($value)
 * @method static Builder|TicketType whereId($value)
 * @method static Builder|TicketType whereName($value)
 * @method static Builder|TicketType whereTitle($value)
 * @method static Builder|TicketType whereUpdatedAt($value)
 * @method static Builder|TicketType whereWorkspaceId($value)
 * @method static \Illuminate\Database\Query\Builder|TicketType withTrashed()
 * @method static \Illuminate\Database\Query\Builder|TicketType withoutTrashed()
 * @mixin Eloquent
 */
class TicketType extends Model
{
    use HasFactory, SoftDeletes, BelongsToWorkspace;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'title',
        'workspace_id',
        'author_id',
    ];

    /**
     * @param $query
     * @param array $filters
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%');
            });
        });
    }

    /**
     * Author of the ticket type.
     *
     * @return BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all the ticket type's fields.
     *
     * @return MorphMany
     */
    public function ticketFields()
    {
        return $this->morphMany(TicketField::class, 'ticket_field');
    }

    /**
     * Get all the ticket type's default fields.
     *
     * @return MorphMany
     */
    public function defaultTicketFields()
    {
        return $this->ticketFields()->whereIn('type', TicketField::defaultTypes());
    }

    /**
     * Get all the ticket type's custom fields.
     *
     * @return MorphMany
     */
    public function customTicketFields()
    {
        return $this->ticketFields()->whereIn('type', TicketField::customTypes());
    }

    /**
     * Get all the tickets of this type.
     *
     * @return HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function generateNewName()
    {
        $name = $this->name;

        do {
            // If the name contains the word 'copy' create the next one
            if (Str::contains($name, 'copy')) {

                //Get the last word
                $words = explode(' ', $name);

                $lastWord = array_pop($words);

                // If the last word is 'copy' create the right number, else it's the first copy
                if ($lastWord == 'copy') {
                    $name = Str::finish($name, ' 2');
                } elseif (is_numeric($lastWord)) {
                    $number = strval(intval($lastWord) + 1);

                    $name = Str::finish(implode(' ', $words), ' '.$number);
                } else {
                    // Else create the first one
                    $name = Str::finish($name, ' - copy');
                }
            } else {
                // Else create the first one
                $name = Str::finish($name, ' - copy');
            }
        } while (self::whereName($name)->exists());

        return $name;
    }
}

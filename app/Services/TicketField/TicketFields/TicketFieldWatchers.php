<?php

namespace App\Services\TicketField\TicketFields;

use App\Http\Resources\Search\UserResource;
use App\Models\TicketField;
use App\Models\User;
use App\Services\TicketField\TicketFieldDefault;

class TicketFieldWatchers extends TicketFieldDefault
{
    /**
     * Show the ticket field.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        return UserResource::collection($this->ticket->watchers);
    }

    /**
     * Save the ticket field.
     */
    public function save()
    {
        $this->ticket->watchers()->sync($this->newValue());
    }

    /**
     * Get new value.
     *
     * @return mixed
     */
    public function newValue(): mixed
    {
        $value = $this->ticketField->value;

        if (! is_array($value)) {
            $value = json_decode($value);
        }

        if (is_null($value)) {
            return null;
        }

        return  collect($value)->filter()->values()->pluck('id');
    }

    /**
     * Get old value.
     *
     * @return mixed
     */
    public function oldValue(): mixed
    {
        return $this->ticket->watchers->pluck('id');
    }

    /**
     * Add activity log for the ticket field.
     *
     * @return array
     */
    protected function log(): array
    {
        $old = $this->oldValue();
        $new = collect($this->newValue());

        return [
            'type' => TicketField::TYPE_WATCHERS,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue() ? User::whereIn('id', $old->diff($new)->values())->pluck('full_name') : null,
            'new' => $this->newValue() ? User::whereIn('id', $new->diff($old)->values())->pluck('full_name') : null,
        ];
    }
}
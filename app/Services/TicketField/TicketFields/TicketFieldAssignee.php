<?php

namespace App\Services\TicketField\TicketFields;

use App\Http\Resources\Search\UserResource;
use App\Models\TicketField;
use App\Models\User;
use App\Services\TicketField\TicketFieldDefault;

class TicketFieldAssignee extends TicketFieldDefault
{
    /**
     * Show the ticket field.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        return new UserResource($this->ticket->assignee);
    }

    /**
     * Get new value.
     *
     * @return string|null
     */
    public function newValue(): ?string
    {
        $value = $this->ticketField->value;

        if (! is_array($value)) {
            $value = json_decode($value);
        }

        if (is_null($value)) {
            return null;
        }

        return collect($value)->filter()->first()['id'];
    }

    /**
     * Get new value.
     *
     * @return mixed
     */
    public function oldValue(): mixed
    {
        $value = $this->ticketField->getOriginal('value');

        if (! is_array($value)) {
            $value = json_decode($value);
        }

        return collect($value)->filter()->first()?->id;
    }

    /**
     * Save the ticket field.
     */
    public function save()
    {
        $this->ticket->update(['assignee_id' => $this->newValue()]);
    }

    /**
     * Add activity log for the ticket field.
     *
     * @return array
     */
    protected function log(): array
    {
        return [
            'type' => TicketField::TYPE_ASSIGNEE,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue() ? User::find($this->oldValue())?->full_name : null,
            'new' => $this->newValue() ? User::find($this->newValue())?->full_name : null,
        ];
    }
}
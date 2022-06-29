<?php

namespace App\Services\TicketField\TicketFields;

use App\Http\Resources\TimeEntryResource;
use App\Models\TicketField;
use App\Services\TicketField\TicketFieldDefault;

class TicketFieldTimeSpent extends TicketFieldDefault
{
    /**
     * Show the ticket field.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        return [
            'time_spent' => $this->ticket->time_spent
        ];
    }

    /**
     * Save the ticket field.
     */
    public function save()
    {
        //
    }

    /**
     * Add activity log for the ticket field.
     *
     * @return array
     */
    protected function log(): array
    {
        return [
            'type' => TicketField::TYPE_TIME_SPENT,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue(),
            'new' => $this->newValue(),
        ];
    }
}
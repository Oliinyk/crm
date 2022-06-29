<?php

namespace App\Services\TicketField\TicketFields;

use App\Models\TicketField;
use App\Services\TicketField\TicketFieldDefault;

class TicketFieldStartDate extends TicketFieldDefault
{
    /**
     * Show the ticket field.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        return $this->ticket->start_date;
    }

    /**
     * Save the ticket field.
     */
    public function save()
    {
        $this->ticket->update(['start_date' => $this->newValue()]);
    }

    /**
     * Add activity log for the ticket field.
     *
     * @return mixed
     */
    protected function log(): array
    {
        return [
            'type' => TicketField::TYPE_START_DATE,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue(),
            'new' => $this->newValue(),
        ];
    }
}
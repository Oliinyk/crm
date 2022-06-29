<?php

namespace App\Services\TicketField\TicketFields;

use App\Models\TicketField;
use App\Services\TicketField\TicketFieldDefault;

class TicketFieldProgress extends TicketFieldDefault
{
    /**
     * Show the ticket field.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        return $this->ticket->progress;
    }

    /**
     * Save the ticket field.
     */
    public function save()
    {
        $this->ticket->update(['progress' => $this->newValue()]);
    }

    /**
     * Add activity log for the ticket field.
     *
     * @return mixed
     */
    protected function log(): array
    {
        return [
            'type' => TicketField::TYPE_PROGRESS,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue(),
            'new' => $this->newValue(),
        ];
    }
}
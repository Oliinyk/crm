<?php

namespace App\Services\TicketField\TicketFields;

use App\Models\TicketField;
use App\Services\TicketField\TicketFieldDefault;

class TicketFieldDueDate extends TicketFieldDefault
{
    /**
     * Show the ticket field.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        return $this->ticket->due_date;
    }

    /**
     * Save the ticket field.
     */
    public function save()
    {
        $this->ticket->update(['due_date' => $this->newValue()]);
    }

    /**
     * Get old value.
     *
     * @return mixed
     */
    public function oldValue(): mixed
    {
        return $this->ticket->due_date?->toDateString();
    }

    /**
     * Add activity log for the ticket field.
     *
     * @return array
     */
    protected function log(): array
    {
        return [
            'type' => TicketField::TYPE_DUE_DATE,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue(),
            'new' => $this->newValue(),
        ];
    }
}
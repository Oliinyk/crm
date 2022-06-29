<?php

namespace App\Services\TicketField\TicketFields;

use App\Models\TicketField;
use App\Services\TicketField\TicketFieldDefault;

class TicketFieldEstimate extends TicketFieldDefault
{
    /**
     * Show the ticket field.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        return $this->ticket->time_estimate;
    }

    /**
     * Save the ticket field.
     */
    public function save()
    {
        $this->ticket->update(['time_estimate' => $this->newValue()]);
    }

    /**
     * Get old value.
     *
     * @return mixed
     */
    public function oldValue(): mixed
    {
        return $this->ticket->time_estimate;
    }

    /**
     * Add activity log for the ticket field.
     *
     * @return array
     */
    protected function log(): array
    {
        return [
            'type' => TicketField::TYPE_ESTIMATE,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue(),
            'new' => $this->newValue(),
        ];
    }
}
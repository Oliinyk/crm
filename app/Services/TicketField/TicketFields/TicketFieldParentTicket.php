<?php

namespace App\Services\TicketField\TicketFields;

use App\Models\Ticket;
use App\Models\TicketField;
use App\Services\TicketField\TicketFieldDefault;

class TicketFieldParentTicket extends TicketFieldDefault
{
    /**
     * Show the ticket field.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        return $this->ticket->parentTicket;
    }

    /**
     * Save the ticket field.
     */
    public function save()
    {
        $this->ticket->update(['parent_ticket_id' => $this->newValue()]);
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

        return collect($value)->filter()->first();
    }

    /**
     * Add activity log for the ticket field.
     *
     * @return array
     */
    protected function log(): array
    {
        return [
            'type' => TicketField::TYPE_PARENT_TICKET,
            'name' => $this->ticketField->name,
            'old' => $this->oldValue() ? Ticket::find($this->oldValue())?->title : null,
            'new' => $this->newValue() ? Ticket::find($this->newValue())?->title : null,
        ];
    }
}
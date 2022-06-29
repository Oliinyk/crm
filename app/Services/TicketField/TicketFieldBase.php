<?php

namespace App\Services\TicketField;

use App\Models\Ticket;
use App\Models\TicketField;

abstract class TicketFieldBase
{
    protected TicketField $ticketField;
    protected Ticket $ticket;

    /**
     * @param Ticket $ticket
     * @param TicketField $ticketField
     */
    public function __construct(Ticket $ticket, TicketField $ticketField)
    {
        $this->ticketField = $ticketField;
        $this->ticket = $ticket;
    }

    /**
     * Process the ticket field.
     */
    public function handle()
    {
        if ($this->newValue() != $this->oldValue()) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($this->ticket)
                ->event('updated')
                ->withProperties($this->log())
                ->log('updated');
        }

        $this->save();
    }

    /**
     * Show the ticket field.
     *
     * @return mixed
     */
    public function show(): mixed
    {
        return $this->newValue();
    }

    /**
     * Get new value.
     *
     * @return mixed
     */
    protected function newValue(): mixed
    {
        return $this->ticketField->value;
    }

    /**
     * Get old value.
     *
     * @return mixed
     */
    protected function oldValue(): mixed
    {
        return $this->ticketField->getOriginal('value');
    }

    /**
     * Save the ticket field.
     */
    abstract protected function save();

    /**
     * Add activity log for the ticket field.
     *
     * @return array
     */
    protected function log(): array
    {
        return [];
    }
}
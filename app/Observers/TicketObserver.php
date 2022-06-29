<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Notifications\TicketAssigneeWasChangedNotification;
use App\Notifications\TicketFieldsWasChangedNotification;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     *
     * @param Ticket $ticket
     * @return void
     */
    public function created(Ticket $ticket)
    {
        //
    }

    /**
     * Handle the Ticket "updated" event.
     *
     * @param Ticket $ticket
     * @return void
     */
    public function updated(Ticket $ticket)
    {
        if ($ticket->isDirty('assignee_id') && ! is_null($ticket->assignee_id) && auth()->check()) {
            $ticket->assignee->notify(new TicketAssigneeWasChangedNotification($ticket, auth()->user()));
        }

        $attributes = [
            'parent_ticket_id',
            'layer_id',
            'title',
            'status',
            'priority',
            'progress',
            'watchers',
            'start_date',
            'due_date',
            'time_estimate',
            'time_spent',
        ];

        if ($ticket->wasChanged($attributes) && ! is_null($ticket->assignee_id) && auth()->user()) {
            $ticket->assignee->notify(new TicketFieldsWasChangedNotification($ticket, auth()->user()));
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     *
     * @param Ticket $ticket
     * @return void
     */
    public function deleted(Ticket $ticket)
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     *
     * @param Ticket $ticket
     * @return void
     */
    public function restored(Ticket $ticket)
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     *
     * @param Ticket $ticket
     * @return void
     */
    public function forceDeleted(Ticket $ticket)
    {
        //
    }
}

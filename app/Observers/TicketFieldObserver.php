<?php

namespace App\Observers;

use App\Models\TicketField;
use App\Services\TicketField\TicketFieldProvider;

class TicketFieldObserver
{

    /**
     * Handle the TicketField "created" event.
     *
     * @param TicketField $ticketField
     * @return void
     */
    public function created(TicketField $ticketField)
    {
        (new TicketFieldProvider($ticketField))->save();
    }

    /**
     * Handle the TicketField "updated" event.
     *
     * @param TicketField $ticketField
     * @return void
     */
    public function updated(TicketField $ticketField)
    {
        (new TicketFieldProvider($ticketField))->save();
    }

    /**
     * Handle the TicketField "deleted" event.
     *
     * @param TicketField $ticketField
     * @return void
     */
    public function deleted(TicketField $ticketField)
    {
        //
    }

    /**
     * Handle the TicketField "restored" event.
     *
     * @param TicketField $ticketField
     * @return void
     */
    public function restored(TicketField $ticketField)
    {
        //
    }

    /**
     * Handle the TicketField "force deleted" event.
     *
     * @param TicketField $ticketField
     * @return void
     */
    public function forceDeleted(TicketField $ticketField)
    {
        //
    }
}

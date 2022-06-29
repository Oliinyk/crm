<?php

namespace App\Observers;

use App\Enums\TimeEntryTypeEnum;
use App\Models\TimeEntry;

class TimeEntryObserver
{
    /**
     * Handle the TimeEntry "created" event.
     *
     * @param TimeEntry $timeEntry
     * @return void
     */
    public function created(TimeEntry $timeEntry)
    {
        $ticket = $timeEntry->ticket;

        $time = null;

        if ($timeEntry->type == TimeEntryTypeEnum::ESTIMATE->value) {
            $time = $timeEntry->time->totalMinutes.'m';
        } else {
            $time = $ticket->timeEntries()
                    ->where('type', '=', $timeEntry->type)
                    ->sum('time').'m';
        }

        $ticket->update([
            "time_{$timeEntry->type}" => $time
        ]);
    }

    /**
     * Handle the TimeEntry "updated" event.
     *
     * @param TimeEntry $timeEntry
     * @return void
     */
    public function updated(TimeEntry $timeEntry)
    {
        //
    }

    /**
     * Handle the TimeEntry "deleted" event.
     *
     * @param TimeEntry $timeEntry
     * @return void
     */
    public function deleted(TimeEntry $timeEntry)
    {
        $ticket = $timeEntry->ticket;

        $ticket->update([
            'time_spent' => $ticket->timeEntries()->sum('time').'m'
        ]);
    }

    /**
     * Handle the TimeEntry "restored" event.
     *
     * @param TimeEntry $timeEntry
     * @return void
     */
    public function restored(TimeEntry $timeEntry)
    {
        //
    }

    /**
     * Handle the TimeEntry "force deleted" event.
     *
     * @param TimeEntry $timeEntry
     * @return void
     */
    public function forceDeleted(TimeEntry $timeEntry)
    {
        //
    }
}

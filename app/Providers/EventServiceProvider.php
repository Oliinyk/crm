<?php

namespace App\Providers;

use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use App\Observers\TicketFieldObserver;
use App\Observers\TicketObserver;
use App\Observers\TimeEntryObserver;
use App\Observers\UserObserver;
use App\Observers\WorkspaceObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Workspace::observe(WorkspaceObserver::class);
        Ticket::observe(TicketObserver::class);
        TicketField::observe(TicketFieldObserver::class);
        TimeEntry::observe(TimeEntryObserver::class);
    }
}

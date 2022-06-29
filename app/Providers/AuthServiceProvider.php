<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Group;
use App\Models\Invitation;
use App\Models\Layer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Policies\ClientPolicy;
use App\Policies\GroupPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\LayerPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RolePolicy;
use App\Policies\TicketPolicy;
use App\Policies\TicketTypePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Group::class => GroupPolicy::class,
        Client::class => ClientPolicy::class,
        Role::class => RolePolicy::class,
        TicketType::class => TicketTypePolicy::class,
        Ticket::class => TicketPolicy::class,
        Layer::class => LayerPolicy::class,
        Invitation::class => InvitationPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('viewWebSocketsDashboard', function ($user = null) {
            return in_array($user->email, [
                'johndoe@example.com'
            ]);
        });
    }
}

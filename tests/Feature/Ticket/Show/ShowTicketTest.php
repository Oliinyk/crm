<?php

namespace Tests\Feature\Ticket\Show;

use App\Enums\PermissionsEnum;
use App\Models\Client;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ShowTicketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Ticket|Collection|Model
     */
    public $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->user = User::factory()->create();

        $project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'owner_id' => $this->user->id,
            'client_id' => Client::factory()->create([
                'workspace_id' => $this->user->workspace_id,
            ]),
        ]);

        $project->members()->attach($this->user->id);

        $ticketType = TicketType::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'author_id' => $this->user->id,
        ]);

        $this->ticket = Ticket::factory([
            'workspace_id' => $this->user->workspace_id,
            'ticket_type_id' => $ticketType->id,
            'project_id' => $project->id,
            'author_id' => $this->user->id,
            'assignee_id' => $this->user->id,
            'status' => Ticket::STATUS_OPEN,
        ])->create();
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->removeRole('Administrator');
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
    }

    /**
     * @test
     */
    public function user_can_see_ticket()
    {
        $this->show()
            ->assertStatus(200)
            ->assertSessionHasNoErrors()->assertInertia(fn (Assert $page) => $page
                ->component('Tickets/Index')
                ->has('modals.0', fn (Assert $page) => $page
                    ->where('component', 'TicketDetailsModal')
                    ->has('ticket', fn (Assert $page) => $page
                        ->where('id', $this->ticket->id)
                        ->where('title', $this->ticket->title)
                        ->etc()
                    )
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_cant_see_ticket_without_permission()
    {
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);
        $this->show()->assertStatus(403);

        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->show()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_can_see_joined_ticket()
    {
        $anotherUser = User::factory()->create();
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->user->givePermissionTo(PermissionsEnum::SEE_JOINED_TICKETS->value);

        $this->ticket->update(['author_id' => $anotherUser->id, 'assignee_id' => $anotherUser->id,]);

        $this->show()->assertStatus(403);

        $this->ticket->update(['author_id' => $this->user->id,]);

        $this->show()->assertStatus(200);

        $this->ticket->update([
            'author_id' => $anotherUser->id,
            'assignee_id' => $this->user->id,
        ]);

        $this->show()->assertStatus(200);
    }

    /**
     * @param array $overwtites
     * @return TestResponse
     */
    public function show($overwtites = [])
    {
        $route = route('ticket.show', [
            'project' => $this->ticket->project_id,
            'ticketType' => $this->ticket->ticket_type_id,
            'ticket' => $this->ticket->id,
            'workspace' => $this->user->workspace_id
        ]);

        return $this->actingAs($this->user)
            ->get($route, array_merge([], $overwtites));
    }
}

<?php

namespace Tests\Feature\TimeEntries\Spent\Show;

use App\Models\Client;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowTimeSpentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Project|Collection|Model
     */
    public $project;

    /**
     * @var TicketField|Collection|Model
     */
    public $ticketField;

    /**
     * @var Ticket|Collection|Model|mixed
     */
    public $ticket;

    /**
     * @var TimeEntry|Collection|Model|mixed
     */
    public $timeEntry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'owner_id' => $this->user->id,
            'client_id' => Client::factory()->create([
                'workspace_id' => $this->user->workspace_id,
            ]),
            'working_hours' => 8
        ]);

        $this->project->members()->attach($this->user->id);

        User::withoutEvents(function () {
            $ticketType = TicketType::factory()->create([
                'workspace_id' => $this->user->workspace_id,
                'author_id' => $this->user->id,
            ]);

            $this->ticket = Ticket::factory([
                'workspace_id' => $this->user->workspace_id,
                'ticket_type_id' => $ticketType->id,
                'project_id' => $this->project->id,
                'author_id' => $this->user->id,
                'assignee_id' => $this->user->id,
                'status' => Ticket::STATUS_OPEN,
            ])->create();

            $this->ticketField = TicketField::factory()->create([
                'workspace_id' => $this->user->workspace_id,
                'type' => TicketField::TYPE_TIME_SPENT,
                'name' => TicketField::TYPE_TIME_SPENT,
                'ticket_field_type' => TicketType::class,
                'ticket_field_id' => $this->ticket->id,
                'value' => null,
            ]);

            $this->timeEntry = TimeEntry::factory()->create([
                'workspace_id' => $this->user->workspace_id,
                'ticket_id' => $this->ticket->id,
                'author_id' => $this->user->id,
            ]);

            $this->user->removeRole('Administrator');
            $this->user->givePermissionTo(Permission::SEE_ALL_PROJECTS);
            $this->user->givePermissionTo(Permission::SEE_ALL_TICKETS);

        });


    }

    /**
     * @test
     */
    public function user_cant_see_time_entries_without_permission()
    {
        $this->user->revokePermissionTo(Permission::SEE_ALL_TICKETS);
        $this->show()->assertStatus(403);

        $this->user->givePermissionTo(Permission::SEE_ALL_TICKETS);
        $this->user->revokePermissionTo(Permission::SEE_ALL_PROJECTS);
        $this->show()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_can_see_time_entries()
    {
        $this->withoutExceptionHandling();

        $this->show()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
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
     * @param array $overwtites
     * @return TestResponse
     */
    public function show()
    {
        $route = route('ticket.time-spent.index', [
            'project' => $this->project->id,
            'workspace' => $this->user->workspace_id,
            'ticket' => $this->ticket,
        ]);

        return $this->actingAs($this->user)
            ->get($route);
    }
}

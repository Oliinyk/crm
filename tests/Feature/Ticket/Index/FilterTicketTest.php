<?php

namespace Tests\Feature\Ticket\Index;

use App\Enums\PermissionsEnum;
use App\Models\Layer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FilterTicketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Ticket
     */
    public $ticket;

    /**
     * @var Project
     */
    public $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->ticket = Ticket::factory()->create([
            'project_id' => $this->project,
            'author_id' => $this->user->id,
            'workspace_id' => $this->user->workspace_id,
            'updated_at' => Carbon::parse('01-01-2020'),
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);

        Ticket::factory()->count(10)->create([
            'status' => Ticket::STATUS_OPEN,
            'priority' => Ticket::PRIORITY_LOW,
            'workspace_id' => $this->user->workspace_id,
            'project_id' => $this->project->id,
        ]);

        TicketField::factory()->count(10)->create([
            'workspace_id' => $this->user->workspace_id,
            'ticket_field_type' => Ticket::class,
            'ticket_field_id' => $this->ticket->id,
            'type' => TicketField::TYPE_NUMERAL,
            'value' => 1
        ]);
    }

    /**
     * @test
     */
    public function user_can_filter_ticket_by_author()
    {
        $this->search(['author_id' => $this->user->id]);
    }

    /**
     * @test
     */
    public function user_can_filter_ticket_by_status()
    {
        $this->ticket->update(['status' => Ticket::STATUS_RESOLVED]);

        $this->search(['status' => Ticket::STATUS_RESOLVED]);
    }

    /**
     * @test
     */
    public function user_can_filter_ticket_by_priority()
    {
        $this->ticket->update(['priority' => Ticket::PRIORITY_HIGH]);

        $this->search(['priority' => Ticket::PRIORITY_HIGH]);
    }

    /**
     * @test
     */
    public function user_can_filter_ticket_by_assignee()
    {
        $assignee = User::factory([
            'workspace_id' => $this->user->workspace_id,
        ])->create();

        $this->ticket->update(['assignee_id' => $assignee->id]);

        $this->search([TicketField::TYPE_ASSIGNEE => [$assignee->id]]);
    }

    /**
     * @test
     */
    public function user_can_filter_ticket_by_layers()
    {
        $layer = Layer::factory([
            'workspace_id' => $this->user->workspace_id,
        ])->create();

        $this->ticket->update(['layer_id' => $layer->id]);

        $this->search([TicketField::TYPE_LAYER => [$layer->id]]);
    }

    /**
     * @test
     */
    public function user_can_filter_ticket_by_watchers()
    {
        $watcher = User::factory([
            'workspace_id' => $this->user->workspace_id,
        ])->create();

        $this->ticket->watchers()->attach($watcher->id);

        $this->search([TicketField::TYPE_WATCHERS => [$watcher->id]]);
    }

    /**
     * @test
     */
    public function user_can_filter_ticket_by_start_date()
    {
        $this->ticket->update(['start_date' => Carbon::parse('01-01-2020')]);
        $this->search(['start_date' => ['31-12-2019', '02-01-2020']]);
        $this->search(['start_date' => ['01-01-2020', '01-01-2020']]);
    }

    /**
     * @test
     */
    public function user_can_filter_ticket_by_due_date()
    {
        $this->ticket->update(['due_date' => Carbon::parse('01-01-2020')]);
        $this->search(['due_date' => ['31-12-2019', '02-01-2020']]);
        $this->search(['due_date' => ['01-01-2020', '01-01-2020']]);
    }

    /**
     * @test
     */
    public function user_can_filter_ticket_by_update_date()
    {
        $this->search(['changed' => ['31-12-2019', '02-01-2020']]);
        $this->search(['changed' => ['01-01-2020', '01-01-2020']]);
    }

    public function search($overwrites = [])
    {
        $route = route('ticket.index', array_merge([
            'project' => $this->project,
            'workspace' => $this->user->workspace_id
        ], $overwrites));

        $this->actingAs($this->user)->get($route)
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tickets/Index')
                ->has('tickets.data', 1)
                ->has('tickets.data.0', fn (Assert $page) => $page
                    ->where('id', $this->ticket->id)
                    ->where('title', $this->ticket->title)
                    ->etc()
                )
            );
    }
}

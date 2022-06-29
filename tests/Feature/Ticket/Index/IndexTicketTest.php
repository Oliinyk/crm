<?php

namespace Tests\Feature\Ticket\Index;

use App\Enums\PermissionsEnum;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class IndexTicketTest extends TestCase
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
            'workspace_id' => $this->user->workspace_id,
            'assignee_id' => $this->user->id,
            'author_id' => $this->user->id,
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);

        Ticket::factory()->count(10)->create([
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
    public function user_can_see_tickets()
    {
        $this->index()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tickets/Index')
                ->has('tickets.data', 10)
                ->has('tickets.data.0', fn (Assert $page) => $page
                    ->where('id', $this->ticket->id)
                    ->where('title', $this->ticket->title)
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_see_all_tickets()
    {
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);

        $this->index()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_see_joined_tickets()
    {
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);
        $this->user->givePermissionTo(PermissionsEnum::SEE_JOINED_TICKETS->value);
        $this->index()->assertStatus(200);

        /**
         * If user is only a ticket author.
         */
        $this->ticket->update([
            'assignee_id' => null,
            'author_id' => $this->user->id,
        ]);

        $this->index()->assertStatus(200);

        /**
         * If user is only a ticket assignee.
         */
        $this->ticket->update([
            'author_id' => User::factory()->create(['workspace_id' => $this->user->workspace_id])->id,
            'assignee_id' => $this->user->id,
        ]);

        $this->index()->assertStatus(200);

        $this->user->removeRole('Administrator');
        $this->user->revokePermissionTo(PermissionsEnum::SEE_JOINED_TICKETS->value);
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);

        $this->index()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_can_search_ticket_by_title()
    {
        $this->ticket->update(['title' => 'lol kek']);

        $this->search(['search' => 'lol kek']);
    }

    /**
     * @test
     */
    public function user_can_search_tickets_by_short_text_custom_field()
    {
        $searchValue = 'TESTTEST';

        TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'ticket_field_type' => Ticket::class,
            'ticket_field_id' => $this->ticket->id,
            'type' => TicketField::TYPE_SHORT_TEXT,
            'value' => $searchValue,
            'string_value' => $searchValue,
        ]);

        $this->search(['search' => $searchValue]);
    }

    /**
     * @test
     */
    public function user_can_search_tickets_by_long_text_custom_field()
    {
        $searchValue = 'TESTTEST';

        TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'ticket_field_type' => Ticket::class,
            'ticket_field_id' => $this->ticket->id,
            'type' => TicketField::TYPE_LONG_TEXT,
            'value' => $searchValue,
            'text_value' => $searchValue,
        ]);

        $this->search(['search' => $searchValue]);
    }

    /**
     * @test
     */
    public function user_can_search_tickets_by_numeral_custom_field()
    {
        $searchValue = 1488;

        TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'ticket_field_type' => Ticket::class,
            'ticket_field_id' => $this->ticket->id,
            'type' => TicketField::TYPE_NUMERAL,
            'value' => $searchValue,
            'int_value' => $searchValue,
        ]);

        $this->search(['search' => $searchValue]);
    }

    /**
     * @test
     */
    public function user_can_search_tickets_by_decimal_custom_field()
    {
        $searchValue = 14.88;

        TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'ticket_field_type' => Ticket::class,
            'ticket_field_id' => $this->ticket->id,
            'type' => TicketField::TYPE_DECIMAL,
            'value' => $searchValue,
            'decimal_value' => $searchValue,
        ]);

        $this->search(['search' => $searchValue]);
    }

    /**
     * @test
     */
    public function user_can_search_tickets_by_date_custom_field()
    {
        $searchValue = '2022-01-01';

        TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'ticket_field_type' => Ticket::class,
            'ticket_field_id' => $this->ticket->id,
            'type' => TicketField::TYPE_DATE,
            'value' => $searchValue,
            'date_value' => $searchValue,
        ]);

        $this->search(['search' => $searchValue]);
    }

    /**
     * @test
     */
    public function user_can_search_tickets_by_time_custom_field()
    {
        $searchValue = '12:00';

        TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'ticket_field_type' => Ticket::class,
            'ticket_field_id' => $this->ticket->id,
            'type' => TicketField::TYPE_TIME,
            'value' => $searchValue,
            'time_value' => $searchValue,
        ]);

        $this->search(['search' => $searchValue]);
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

    /**
     * @return TestResponse
     */
    public function index($overwrites = [])
    {
        $route = route('ticket.index', array_merge([
            'project' => $this->project,
            'workspace' => $this->user->workspace_id
        ], $overwrites));

        return $this->actingAs($this->user)->get($route);
    }
}

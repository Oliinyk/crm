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
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SearchTicketTest extends TestCase
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
    public function user_can_search_ticket_by_title()
    {
        $this->ticket->update(['title' => 'lol kek']);

        $this->search(['search' => 'lol kek'])->assertJsonFragment([
            'data' => [
                [
                    'id' => $this->ticket->id,
                    'name' => $this->ticket->title,
                ],
            ],
        ])->assertJsonCount(1, 'data');
    }

    /**
     * @test
     */
    public function user_can_search_ticket_by_title_with_excluded_selected_options()
    {
        $this->ticket->update(['title' => 'lol kek']);

        Ticket::factory()->count(9)->create([
            'title' => 'lol kek',
            'workspace_id' => $this->user->workspace_id,
            'project_id' => $this->project->id,
        ]);

        $this->search([
            'search' => 'lol kek',
            'selectedOptions' => [$this->ticket->toJson()],
        ])->assertStatus(200)
            ->assertJsonCount(9, 'data');
    }

    public function search($overwrites = [])
    {
        $route = route('ticket.search', array_merge([
            'project' => $this->project,
            'workspace' => $this->user->workspace_id
        ], $overwrites));

        return $this->actingAs($this->user)->get($route);
    }
}

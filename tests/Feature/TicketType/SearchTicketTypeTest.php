<?php

namespace Tests\Feature\TicketType;

use App\Enums\PermissionsEnum;
use App\Models\Project;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SearchTicketTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var TicketType
     */
    public $ticketType;

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

        $this->ticketType = TicketType::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);
        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->givePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);

        TicketType::factory()->count(10)->create([
            'workspace_id' => $this->user->workspace_id,
        ]);
    }

    /**
     * @test
     */
    public function user_can_search_ticket_type_by_title()
    {
        $this->ticketType->update(['title' => 'lol kek']);

        $this->search(['search' => 'lol kek'])
            ->assertJsonFragment([
                'data' => [
                    [
                        'id' => $this->ticketType->id,
                        'name' => $this->ticketType->name,
                    ],
                ],
            ])->assertJsonCount(1, 'data');
    }

    /**
     * @test
     */
    public function user_can_search_ticket_by_title_with_excluded_selected_options()
    {
        $this->ticketType->update(['title' => 'lol kek']);

        TicketType::factory()->count(9)->create([
            'title' => 'lol kek',
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->search([
            'search' => 'lol kek',
            'selectedOptions' => [$this->ticketType->toJson()],
        ])->assertStatus(200)
            ->assertJsonCount(9, 'data');
    }

    public function search($overwrites = [])
    {
        $route = route('ticket-type.search', array_merge([
            'workspace' => $this->user->workspace_id,
            'project' => $this->project,
        ], $overwrites));

        return $this->actingAs($this->user)->get($route);
    }
}

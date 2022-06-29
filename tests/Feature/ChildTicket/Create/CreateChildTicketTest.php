<?php

namespace Tests\Feature\ChildTicket\Create;

use App\Enums\PermissionsEnum;
use App\Models\Client;
use App\Models\Layer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class CreateChildTicketTest extends TestCase
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
     * @var Ticket|Collection|Model
     */
    public $parentTicket;

    /**
     * @var Layer|Collection|Model
     */
    public $layer;

    /**
     * @var TicketField|Collection|Model
     */
    public $ticketField;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();

        $this->project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'owner_id' => $this->user->id,
            'client_id' => Client::factory()->create([
                'workspace_id' => $this->user->workspace_id,
            ]),
        ]);

        $this->project->members()->attach($this->user->id);

        $ticketType = TicketType::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'author_id' => $this->user->id,
        ]);

        $this->parentTicket = Ticket::factory([
            'workspace_id' => $this->user->workspace_id,
            'ticket_type_id' => $ticketType->id,
            'project_id' => $this->project->id,
            'author_id' => $this->user->id,
            'assignee_id' => $this->user->id,
        ])->create();

        $this->layer = Layer::factory([
            'workspace_id' => $this->user->workspace_id,
            'project_id' => $this->project->id,
            'author_id' => $this->user->id,
        ])->create();

        $this->ticketField = TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'type' => TicketField::TYPE_SHORT_TEXT,
            'name' => 'test',
            'ticket_field_type' => TicketType::class,
            'ticket_field_id' => $ticketType->id,
            'value' => null,
        ]);

        $this->user->removeRole('Administrator');
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->user->givePermissionTo(PermissionsEnum::CREATE_TICKETS->value);
    }

    /**
     * @test
     */
    public function user_can_create_a_child_ticket()
    {
        $this->assertDatabaseCount('tickets', 1);

        $this->create()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('tickets', 2);
    }

    /**
     * @test
     */
    public function user_can_create_a_child_tickets_title()
    {
        $this->create(['title' => 'test'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'title' => 'test',
            'parent_ticket_id' => $this->parentTicket->id
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_a_ticket()
    {
        $this->user->revokePermissionTo(PermissionsEnum::CREATE_TICKETS->value);

        $this->create()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_must_record_activity()
    {
        $this->assertDatabaseCount(Activity::class, 1);

        $this->create();

        $this->assertDatabaseCount(Activity::class, 3);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'created',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id
        ]);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => 'title',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_CHILD_TICKET,
            'properties->name' => TicketField::TYPE_CHILD_TICKET
        ]);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        $data = array_merge([
            'ticket_type' => $this->ticketField->ticketField->id,
            'title' => 'title',
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => $this->ticketField->value,
                ],
            ],
        ], $overwtites);

        $route = route('ticket.child.store', [
            'project' => $this->project->id,
            'workspace' => $this->user->workspace_id,
            'ticket' => $this->parentTicket->id
        ]);

        return $this->actingAs($this->user)
            ->post($route, $data);
    }
}

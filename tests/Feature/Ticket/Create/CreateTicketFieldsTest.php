<?php

namespace Tests\Feature\Ticket\Create;

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
use Tests\TestCase;

class CreateTicketFieldsTest extends TestCase
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
    public function ticket_must_copy_a_custom_ticket_fields_with_values_from_ticket_type()
    {
        $this->create(['fields' => [[
            'name' => 'test',
            'type' => TicketField::TYPE_SHORT_TEXT,
            'order' => 1,
            'value' => 'test',
        ]]])->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ticket_fields', [
            'name' => 'test',
            'order' => 1,
            'value' => 'test',
            'type' => TicketField::TYPE_SHORT_TEXT,
            'ticket_field_type' => Ticket::class,
        ]);
    }

    /**
     * @test
     */
    public function ticket_must_copy_a_default_ticket_fields_from_ticket_type()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $this->create()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ticket_fields', [
            'order' => 1,
            'type' => TicketField::TYPE_STATUS,
            'ticket_field_type' => Ticket::class,
        ]);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        $data = array_merge([
            'title' => 'title',
            'ticket_type' => $this->ticketField->ticketField->id,
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => $this->ticketField->value,
                ],
            ],
        ], $overwtites);

        $route = route('ticket.store', [
            'project' => $this->project->id,
            'workspace'=>$this->user->workspace_id
        ]);

        return $this->actingAs($this->user)
            ->post($route, $data);
    }
}

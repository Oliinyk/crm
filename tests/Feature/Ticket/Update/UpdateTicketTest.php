<?php

namespace Tests\Feature\Ticket\Update;

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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UpdateTicketTest extends TestCase
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

        $this->parentTicket = Ticket::factory([
            'workspace_id' => $this->user->workspace_id,
            'ticket_type_id' => $ticketType->id,
            'project_id' => $project->id,
            'author_id' => $this->user->id,
            'assignee_id' => $this->user->id,
        ])->create();

        $this->ticket = Ticket::factory([
            'workspace_id' => $this->user->workspace_id,
            'ticket_type_id' => $ticketType->id,
            'project_id' => $project->id,
            'author_id' => $this->user->id,
            'assignee_id' => $this->user->id,
            'status' => Ticket::STATUS_OPEN,
        ])->create();

        $this->layer = Layer::factory([
            'workspace_id' => $this->user->workspace_id,
            'project_id' => $project->id,
            'author_id' => $this->user->id,
        ])->create();

        $this->ticketField = TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'type' => TicketField::TYPE_STATUS,
            'name' => '',
            'ticket_field_type' => Ticket::class,
            'ticket_field_id' => $this->ticket->id,
            'value' => null,
        ]);

        $this->user->removeRole('Administrator');
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->user->givePermissionTo(PermissionsEnum::EDIT_ALL_TICKETS->value);
        $this->user->givePermissionTo(PermissionsEnum::EDIT_ASSIGNEE_TICKETS->value);
    }

    /**
     * @test
     */
    public function user_can_update_a_ticket_status_and_media()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $file = UploadedFile::fake()->image('avatar.jpg');
        $file = Storage::disk('public')->putFileAs('temp/', $file, $file->getClientOriginalName());

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
            'status' => Ticket::STATUS_RESOLVED,
        ]);

        $this->assertDatabaseMissing('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => Ticket::STATUS_RESOLVED,
        ]);

        $this->update(['media' => [['url' => $file]], 'value' => Ticket::STATUS_RESOLVED])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'status' => Ticket::STATUS_RESOLVED,
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => Ticket::STATUS_RESOLVED,
        ]);

        $media = $this->ticket->getFirstMedia();

        Storage::disk('public')->assertExists($media->id.'/'.$media->file_name);
    }

    /**
     * @test
     */
    public function user_can_update_a_ticket_priority()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PRIORITY]);

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
            'priority' => Ticket::PRIORITY_LOW,
        ]);

        $this->assertDatabaseMissing('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => Ticket::PRIORITY_LOW,
        ]);

        $this->update(['value' => Ticket::PRIORITY_LOW])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'priority' => Ticket::PRIORITY_LOW,
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => Ticket::PRIORITY_LOW,
        ]);
    }

    /**
     * @test
     */
    public function user_can_update_a_ticket_layer_id()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_LAYER]);

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
            'layer_id' => $this->layer->id,
        ]);

        $this->assertDatabaseMissing('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => [$this->layer->id],
        ]);

        $this->update(['value' => [$this->layer->id]])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'layer_id' => $this->layer->id,
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => json_encode([$this->layer->id]),
        ]);
    }

    /**
     * @test
     */
    public function user_can_update_a_ticket_parent_ticket_id()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PARENT_TICKET]);

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
            'parent_ticket_id' => $this->parentTicket->id,
        ]);

        $this->assertDatabaseMissing('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => [$this->parentTicket->id],
        ]);

        $this->update(['value' => [$this->parentTicket->id]])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'parent_ticket_id' => $this->parentTicket->id,
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => json_encode([$this->parentTicket->id]),
        ]);
    }

    /**
     * @test
     */
    public function user_can_update_a_ticket_progress()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PROGRESS]);

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
            'progress' => 95,
        ]);

        $this->assertDatabaseMissing('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => 95,
        ]);

        $this->update(['value' => 95])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'progress' => 95,
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => 95,
        ]);
    }

    /**
     * @test
     */
    public function user_can_update_a_ticket_assignee_id()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ASSIGNEE]);

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
            'assignee_id' => $this->user->id,
        ]);

        $this->assertDatabaseMissing('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => [$this->user->id],
        ]);

        $this->update(['value' => [$this->user->id]])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'assignee_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => json_encode([$this->user->id]),
        ]);
    }

    /**
     * @test
     */
    public function user_can_update_a_ticket_start_date()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_START_DATE]);

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
            'start_date' => '2020-01-21',
        ]);

        $this->assertDatabaseMissing('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => '2020-01-21',
        ]);

        $this->update(['value' => '2020-01-21'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'start_date' => '2020-01-21',
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => '2020-01-21',
        ]);
    }

    /**
     * @test
     */
    public function user_can_update_a_ticket_due_date()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_DUE_DATE]);

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
            'due_date' => '2020-01-21',
        ]);

        $this->assertDatabaseMissing('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => '2020-01-21',
        ]);

        $this->update(['value' => '2020-01-21'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'due_date' => '2020-01-21',
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => '2020-01-21',
        ]);
    }

    /**
     * @test
     */
    public function user_can_update_a_ticket_estimate()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ESTIMATE]);

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
            'time_estimate' => 1,
        ]);

        $this->assertDatabaseMissing('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => 1,
        ]);

        $this->update(['value' => '1m'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'time_estimate' => 1,
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'id' => $this->ticketField->id,
            'value' => 1,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_update_a_ticket()
    {
        $this->ticket->update([
            'assignee_id' => null,
            'author_id' => User::factory()->create()->id,
        ]);

        $this->user->revokePermissionTo(PermissionsEnum::EDIT_ALL_TICKETS->value);
        $this->user->revokePermissionTo(PermissionsEnum::EDIT_ASSIGNEE_TICKETS->value);

        $this->update()->assertStatus(403);
    }

    /**
     * @test
     */
    public function assignee_must_have_permission_to_update_a_ticket()
    {
        auth()->login($this->user);

        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $this->user->revokePermissionTo(PermissionsEnum::EDIT_ALL_TICKETS->value);
        $this->user->givePermissionTo(PermissionsEnum::EDIT_ASSIGNEE_TICKETS->value);
        $this->ticket->update(['assignee_id' => $this->user->id]);

        $this->update()->assertStatus(302);
    }

    /**
     * @test
     */
    public function ticket_author_can_update_ticket_without_permission()
    {
        auth()->login($this->user);
        $this->user->revokePermissionTo(PermissionsEnum::EDIT_ALL_TICKETS->value);
        $this->user->revokePermissionTo(PermissionsEnum::EDIT_ASSIGNEE_TICKETS->value);
        $this->ticket->update(['author_id' => $this->user->id]);
        $this->update()->assertStatus(302);
    }

    /**
     * @param array $overwtites
     * @return TestResponse
     */
    public function update($overwtites = [])
    {
        $route = route('ticket.update', [
            'project' => $this->ticket->project_id,
            'ticket' => $this->ticket->id,
            'workspace' => $this->user->workspace_id
        ]);

        return $this->actingAs($this->user)
            ->put($route, array_merge([
                'id' => $this->ticketField->id,
                'type' => $this->ticketField->type,
                'value' => 'test',
            ], $overwtites));
    }
}

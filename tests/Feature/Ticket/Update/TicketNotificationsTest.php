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
use App\Notifications\TicketAssigneeWasChangedNotification;
use App\Notifications\TicketFieldsWasChangedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TicketNotificationsTest extends TestCase
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
        ])->create();

        $this->layer = Layer::factory([
            'workspace_id' => $this->user->workspace_id,
            'project_id' => $project->id,
            'author_id' => $this->user->id,
        ])->create();

        $this->ticketField = TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'type' => TicketField::TYPE_SHORT_TEXT,
            'name' => 'test',
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
    public function it_must_send_notification_after_assignee_was_changed()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ASSIGNEE]);

        $newUser = User::factory()->create();

        $this->user->workspaces()->first()->members()->attach($newUser);

        $this->ticket->project->members()->attach($newUser->id);

        Notification::fake();

        /**
         * If the assignee is not changed - do not send anything.
         */
        $this->update(['value' => [$this->user->id]])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        Notification::assertNotSentTo($newUser, TicketAssigneeWasChangedNotification::class);

        /**
         * Send a notification to the new assignee.
         */
        $this->update(['value' => [$newUser->id]])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        Notification::assertSentTo(
            [$newUser], function (TicketAssigneeWasChangedNotification $notification, $channels) use ($newUser) {
            return $notification->ticket->id === $this->ticket->id && $notification->user->id == $this->user->id;
        });
    }

    /**
     * @test
     */
    public function it_must_send_notification_after_any_of_the_default_ticket_fields_was_changed()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        Notification::fake();

        /**
         * Send a notification to the new assignee.
         */
        $this->update(['value' => Ticket::STATUS_OPEN])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        Notification::assertSentTo(
            [$this->user], function (TicketFieldsWasChangedNotification $notification, $channels) {
            return $notification->ticket->id === $this->ticket->id;
        });
    }

    /**
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

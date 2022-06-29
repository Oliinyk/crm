<?php

namespace Tests\Feature\ChildTicket\Delete;

use App\Enums\PermissionsEnum;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DeleteChildTicketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var TicketType|Collection|Model
     */
    public $ticketType;

    /**
     * @var Project|Collection|Model
     */
    public $project;

    /**
     * @var Ticket|Collection|Model
     */
    public $ticket;

    /**
     * @var Ticket|Collection|Model
     */
    public $parentTicket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->ticketType = TicketType::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->project->members()->attach($this->user->id);

        $this->parentTicket = Ticket::factory([
            'workspace_id' => $this->user->workspace_id,
        ])->create();

        $this->ticket = Ticket::factory([
            'workspace_id' => $this->user->workspace_id,
        ])->create();

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->user->givePermissionTo(PermissionsEnum::DELETE_ALL_TICKETS->value);
    }

    /**
     * @test
     */
    public function user_can_delete_a_child_ticket()
    {
        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_delete_a_child_ticket_fields()
    {
        $this->ticket->ticketFields()->create([
            'type' => TicketField::TYPE_SHORT_TEXT,
            'name' => 'test',
            'order' => 1,
            'workspace_id' => $this->ticketType->workspace_id,
        ]);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('ticket_fields', 0);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_delete_a_child_ticket()
    {
        $this->user->revokePermissionTo(PermissionsEnum::DELETE_ALL_TICKETS->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_without_permission_can_delete_own_child_ticket()
    {
        $this->ticket->update([
            'author_id' => $this->user->id,
        ]);

        $this->user->revokePermissionTo(PermissionsEnum::DELETE_ALL_TICKETS->value);
        $this->user->givePermissionTo(PermissionsEnum::DELETE_OWN_TICKETS->value);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
        ]);
    }

    /**
     * @test
     */
    public function user_without_any_permission_cant_delete_even_own_ticket()
    {
        $this->ticket->update([
            'author_id' => $this->user->id,
        ]);

        $this->user->revokePermissionTo(PermissionsEnum::DELETE_ALL_TICKETS->value);
        $this->user->revokePermissionTo(PermissionsEnum::DELETE_OWN_TICKETS->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_must_record_activity()
    {
        $this->assertDatabaseCount(Activity::class, 2);

        $this->destroy();

        $this->assertDatabaseCount(Activity::class, 3);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'deleted',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => $this->ticket->title,
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_CHILD_TICKET,
            'properties->name' => TicketField::TYPE_CHILD_TICKET
        ]);
    }

    /**
     * @return TestResponse
     */
    public function destroy()
    {
        $route = route('ticket.child.destroy', [
            'project' => $this->project->id,
            'ticket' => $this->parentTicket->id,
            'child' => $this->ticket->id,
            'workspace' => $this->user->workspace_id
        ]);

        return $this->actingAs($this->user)
            ->delete($route);
    }
}

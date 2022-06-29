<?php

namespace Tests\Feature\Comment;

use App\Enums\PermissionsEnum;
use App\Models\Client;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DeleteCommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Comment|Collection|Model
     */
    public $comment;

    /**
     * @var Ticket|Collection|Model
     */
    public $ticket;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->ticket = Ticket::factory([
            'workspace_id' => $this->user->workspace_id,
            'ticket_type_id' => $ticketType->id,
            'project_id' => $project->id,
            'author_id' => $this->user->id,
            'assignee_id' => $this->user->id,
            'status' => Ticket::STATUS_OPEN,
        ])->create();


        $this->comment = Comment::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'author_id' => $this->user->id,
            'ticket_id' => $this->ticket->id,
        ]);

        $this->user->removeRole('Administrator');

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);

        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
    }

    /**
     * @test
     */
    public function user_can_delete_comment()
    {
        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
        ]);

        $this->destroy()
            ->assertStatus(200)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('comments', [
            'id' => $this->comment->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_delete_project()
    {
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @return TestResponse
     */
    public function destroy()
    {
        $route = route('ticket.comment.destroy', [
            'workspace' => $this->user->workspace_id,
            'project' => $this->ticket->project_id,
            'ticket' => $this->ticket->id,
            'comment' => $this->comment->id
        ]);

        return $this->actingAs($this->user)
            ->delete($route);
    }
}

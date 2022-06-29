<?php

namespace Tests\Feature\Comment;

use App\Models\Client;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CreateCommentTest extends TestCase
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
    }

    /**
     * @test
     */
    public function user_can_create_comment()
    {
        $this->create()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'text' => 'test',
            'workspace_id' => $this->user->workspace_id,
            'ticket_id' => $this->ticket->id,
            'author_id' => $this->user->id
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_a_client()
    {
        $this->user->removeRole('Administrator');

        $this->create()->assertStatus(403);
    }

    /**
     * @test
     */
    public function the_text_field_is_required()
    {
        $this->create(['text' => ''])
            ->assertSessionHasErrors(['text' => 'The text field is required.']);
    }

    /**
     * @test
     */
    public function the_name_may_not_be_greater_than_50_characters()
    {
        $this->create(['text' => Str::repeat('a', 1025)])
            ->assertSessionHasErrors(['text' => 'The text must not be greater than 1024 characters.']);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        $route = route('ticket.comment.store', [
            'workspace' => $this->user->workspace_id,
            'project' => $this->ticket->project_id,
            'ticket' => $this->ticket->id
        ]);

        return $this->actingAs($this->user)
            ->post($route, array_merge([
                'text' => 'test',
            ], $overwtites));
    }
}

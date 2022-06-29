<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Layer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function ticket_belongs_to_workspace()
    {
        $ticket = Ticket::factory()->create();

        $this->assertInstanceOf(Workspace::class, $ticket->workspace);
    }

    /**
     * @test
     */
    public function ticket_belongs_to_the_ticket_type()
    {
        $ticket = Ticket::factory()->create();

        $this->assertInstanceOf(TicketType::class, $ticket->ticketType);
    }

    /**
     * @test
     */
    public function ticket_belongs_to_the_project()
    {
        $ticket = Ticket::factory()->create();

        $this->assertInstanceOf(Project::class, $ticket->project);
    }

    /**
     * @test
     */
    public function ticket_must_have_a_filter_scope()
    {
        Ticket::factory()->count(10)->create();

        $ticket = Ticket::factory()->create([
            'title' => '123123kek',
        ]);

        $result = Ticket::filter(['search' => '123123kek'])->get();

        $this->assertCount(1, $result);
        $this->assertSame($ticket->id, $result->first()->id);
    }

    /**
     * @test
     */
    public function it_must_belongs_to_the_author()
    {
        $ticket = Ticket::factory()->create();

        $this->assertInstanceOf(User::class, $ticket->author);
    }

    /**
     * @test
     */
    public function it_must_belongs_to_the_parent_ticket()
    {
        $this->withoutNotifications();

        $ticket = Ticket::factory()->create();
        $parentTicket = Ticket::factory()->create(['workspace_id' => $ticket->workspace_id]);

        $ticket->update([
            'parent_ticket_id' => $parentTicket->id,
        ]);

        $this->assertInstanceOf(Ticket::class, $ticket->parentTicket);
    }

    /**
     * @test
     */
    public function it_must_has_many_child_tickets()
    {
        $this->withoutNotifications();

        $ticket = Ticket::factory()->create();
        $parentTicket = Ticket::factory()->create(['workspace_id' => $ticket->workspace_id]);

        $ticket->update([
            'parent_ticket_id' => $parentTicket->id,
        ]);

        $this->assertInstanceOf(Ticket::class, $parentTicket->childTickets->first());
        $this->assertSame($ticket->id, $parentTicket->childTickets->first()->id);
    }

    /**
     * @test
     */
    public function it_must_belongs_to_the_assignee()
    {
        $user = User::factory()->create(['id' => 666]);
        $ticket = Ticket::factory([
            'assignee_id' => $user->id,
        ])->create();
        $this->assertInstanceOf(User::class, $ticket->assignee);
        $this->assertSame($user->id, $ticket->assignee->id);
    }

    /**
     * @test
     */
    public function ticket_has_many_watchers()
    {
        $ticket = Ticket::factory()->create();
        $user = User::factory()->create();

        $ticket->watchers()->attach($user);

        $this->assertInstanceOf(User::class, $ticket->watchers->first());
        $this->assertSame($user->id, $ticket->watchers->first()->id);
    }

    /**
     * @test
     */
    public function it_must_belongs_to_the_layer()
    {
        $user = Layer::factory()->create(['id' => 666]);
        $ticket = Ticket::factory([
            'layer_id' => $user->id,
        ])->create();
        $this->assertInstanceOf(Layer::class, $ticket->layer);
        $this->assertSame($user->id, $ticket->layer->id);
    }

    /**
     * @test
     */
    public function ticket_has_many_comments()
    {
        $comment = Comment::factory([
            'ticket_id' => $ticket = Ticket::factory()->create()
        ])->create();

        $this->assertInstanceOf(Comment::class, $ticket->comments()->first());
        $this->assertSame($comment->id, $ticket->comments()->first()->id);
    }

    /**
     * @test
     */
    public function ticket_has_many_time_entries()
    {
        $timeEntry = TimeEntry::factory([
            'ticket_id' => $ticket = Ticket::factory()->create()
        ])->create();

        $this->assertInstanceOf(TimeEntry::class, $ticket->timeEntries()->first());
        $this->assertSame($timeEntry->id, $ticket->timeEntries()->first()->id);
    }

    /**
     * @test
     * @dataProvider timeRemaining
     */
    public function im_must_calculate_time_remaining($estimate, $spent, $result)
    {
        $ticket = Ticket::factory()->create([
            'time_estimate' => $estimate,
            'time_spent' => $spent
        ]);

        $this->assertSame($result, $ticket->time_remaining);
    }

    public function timeRemaining()
    {
        return [
            ['10m', '4m', '6 minutes'],
            ['1h', '30m', '30 minutes'],
            ['1h', '1h', 0],
            [null, '30m', null],
            ['1m', '30m', null],
            ['1m', null, null],
        ];
    }

    /**
     * @test
     * @dataProvider  timePercent
     */
    public function im_must_calculate_time_percent($estimate, $spent, $result)
    {
        $ticket = Ticket::factory()->create([
            'time_estimate' => $estimate,
            'time_spent' => $spent
        ]);

        $this->assertSame($result, $ticket->time_percent);
    }

    public function timePercent()
    {
        return [
            ['10m', '4m', 40],
            ['1h', '30m', 50],
            [null, '30m', 0],
            ['1m', '30m', 101],
            ['1m', null, 0],
        ];
    }
}

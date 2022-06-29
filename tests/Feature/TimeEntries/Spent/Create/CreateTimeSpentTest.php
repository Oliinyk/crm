<?php

namespace Tests\Feature\TimeEntries\Spent\Create;

use App\Enums\PermissionsEnum;
use App\Enums\TimeEntryTypeEnum;
use App\Models\Client;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CreateTimeSpentTest extends TestCase
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
     * @var TicketField|Collection|Model
     */
    public $ticketField;

    /**
     * @var Ticket|Collection|Model|mixed
     */
    public $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'owner_id' => $this->user->id,
            'client_id' => Client::factory()->create([
                'workspace_id' => $this->user->workspace_id,
            ]),
            'working_hours' => 8
        ]);

        $this->project->members()->attach($this->user->id);

        User::withoutEvents(function () {
            $ticketType = TicketType::factory()->create([
                'workspace_id' => $this->user->workspace_id,
                'author_id' => $this->user->id,
            ]);

            $this->ticket = Ticket::factory([
                'workspace_id' => $this->user->workspace_id,
                'ticket_type_id' => $ticketType->id,
                'project_id' => $this->project->id,
                'author_id' => $this->user->id,
                'assignee_id' => $this->user->id,
                'status' => Ticket::STATUS_OPEN,
            ])->create();

            $this->ticketField = TicketField::factory()->create([
                'workspace_id' => $this->user->workspace_id,
                'type' => TicketField::TYPE_TIME_SPENT,
                'name' => TicketField::TYPE_TIME_SPENT,
                'ticket_field_type' => TicketType::class,
                'ticket_field_id' => $this->ticket->id,
                'value' => null,
            ]);
        });


        $this->user->removeRole('Administrator');
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->user->givePermissionTo(PermissionsEnum::CREATE_TICKETS->value);
    }

    /**
     * @test
     */
    public function user_can_create_a_time_entry()
    {
        $this->assertDatabaseCount('time_entries', 0);

        $this->create()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('time_entries', 1);

        $this->assertDatabaseHas('time_entries', [
            'time' => 480,
            'created_at' => '2020-01-01 00:00:00',
            'description' => 'test',
            'author_id' => $this->user->id,
            'workspace_id' => $this->user->workspace_id,
            'ticket_id' => $this->ticket->id
        ]);

    }

    /**
     * @test
     */
    public function ticket_must_have_time_spent()
    {
        $this->create(['time' => '2d'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertSame('2 days', $this->ticket->fresh()->time_spent);

        $this->create(['time' => '2d'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertSame('4 days', $this->ticket->fresh()->time_spent);

    }

    /**
     * @test
     */
    public function user_must_create_time_spent_entry()
    {
        $this->assertDatabaseCount('time_entries', 0);

        $this->create(['time' => '2d'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('time_entries', 1);

        $this->assertDatabaseHas('time_entries', [
            "workspace_id" => $this->user->workspace_id,
            "ticket_id" => $this->ticket->id,
            "author_id" => $this->user->id,
            "type" => TimeEntryTypeEnum::SPENT->value,
            "description" => "test",
            "time" => 960,
        ]);
    }

    /**
     * @test
     */
    public function description_field_is_required()
    {
        $this->create(['description' => ''])
            ->assertSessionHasErrors(['description' => 'The description field is required.']);
    }

    /**
     * @test
     */
    public function date_field_is_required()
    {
        $this->create(['date' => ''])
            ->assertSessionHasErrors(['date' => 'The date field is required.']);
    }

    /**
     * @test
     */
    public function time_field_is_required()
    {
        $this->create(['time' => ''])
            ->assertSessionHasErrors(['time' => 'The time field is required.']);
    }

    /**
     * @test
     */
    public function date_field_must_be_valid()
    {
        $this->create(['date' => 'test'])
            ->assertSessionHasErrors(['date' => 'The date is not a valid date.']);
    }

    /**
     * @test
     * @dataProvider invalidTime
     */
    public function time_must_have_a_valid_format($time)
    {
        $this->create(['time' => $time])
            ->assertSessionHasErrors(['time' => 'The time format is invalid.']);
    }

    public function invalidTime()
    {
        return [
            ['1'],
            ['test'],
            ['123'],
        ];
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        $data = array_merge([
            'date' => '2020-01-01',
            'time' => '1d',
            'description' => 'test'
        ], $overwtites);

        $route = route('ticket.time-spent.store', [
            'project' => $this->project->id,
            'workspace' => $this->user->workspace_id,
            'ticket' => $this->ticket,
        ]);

        return $this->actingAs($this->user)
            ->post($route, $data);
    }
}

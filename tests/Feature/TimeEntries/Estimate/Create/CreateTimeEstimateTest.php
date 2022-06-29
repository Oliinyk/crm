<?php

namespace Tests\Feature\TimeEntries\Estimate\Create;

use App\Enums\TimeEntryTypeEnum;
use App\Models\Client;
use App\Models\Permission;
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

class CreateTimeEstimateTest extends TestCase
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
        $this->user->givePermissionTo(Permission::SEE_ALL_PROJECTS);
        $this->user->givePermissionTo(Permission::CREATE_TICKETS);
    }

    /**
     * @test
     */
    public function user_can_create_a_time_estimate()
    {
        $this->assertDatabaseCount('time_entries', 0);

        $this->create()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('time_entries', 1);

        $this->assertDatabaseHas('time_entries', [
            'time' => 480,
            'description' => 'test',
            'author_id' => $this->user->id,
            'workspace_id' => $this->user->workspace_id,
            'ticket_id' => $this->ticket->id,
            'type' => TimeEntryTypeEnum::ESTIMATE->value
        ]);

    }

    /**
     * @test
     */
    public function ticket_must_have_time_estimate()
    {
        $this->create(['time' => '2d'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertSame('2 days', $this->ticket->fresh()->time_estimate);

        $this->create(['time' => '2d'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertSame('2 days', $this->ticket->fresh()->time_estimate);
    }

    /**
     * @test
     */
    public function user_must_create_time_estimate_entry()
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
            "type" => TimeEntryTypeEnum::ESTIMATE->value,
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
            ->assertSessionDoesntHaveErrors();

        $this->create(['description' => ''])
            ->assertSessionHasErrors(['description' => 'The description field is required.']);
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

        $route = route('ticket.time-estimate.store', [
            'project' => $this->project->id,
            'workspace' => $this->user->workspace_id,
            'ticket' => $this->ticket,
        ]);

        return $this->actingAs($this->user)
            ->post($route, $data);
    }
}

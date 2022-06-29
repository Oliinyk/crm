<?php

namespace Tests\Feature\Ticket\Update;

use App\Enums\PermissionsEnum;
use App\Models\Layer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UpdateTicketFieldsValidationTest extends TestCase
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
     * @var TicketField|Collection|Model
     */
    public $ticketField;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $ticketType = TicketType::factory()->create(['workspace_id' => $this->user->workspace_id]);

        $project = Project::factory()->create(['workspace_id' => $this->user->workspace_id]);

        $project->members()->attach($this->user->id);

        $this->ticket = Ticket::factory([
            'ticket_type_id' => $ticketType->id,
            'workspace_id' => $this->user->workspace_id,
            'project_id' => $project->id,
        ])->create();

        $this->ticketField = TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'type' => TicketField::TYPE_STATUS,
            'name' => '',
            'ticket_field_type' => Ticket::class,
            'ticket_field_id' => $this->ticket->id,
            'value' => null,
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);

        $this->user->givePermissionTo(PermissionsEnum::EDIT_ALL_TICKETS->value);
    }

    /**
     * @test
     */
    public function id_field_is_required()
    {
        $this->update(['id' => ''])
            ->assertSessionHasErrors(['id' => 'The selected id is invalid.']);
    }

    /**
     * @test
     */
    public function id_field_must_be_valid()
    {
        $this->update(['id' => '666'])
            ->assertSessionHasErrors(['id' => 'The selected id is invalid.']);
    }

    /**
     * @test
     */
    public function type_field_must_be_valid()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $this->update(['type' => 'kek'])->assertSessionHasErrors(['type' => 'The selected type is invalid.']);
    }

    /**
     * @test
     */
    public function type_field_must_exists_in_ticket_field()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $this->update(['type' => TicketField::TYPE_SHORT_TEXT])
            ->assertSessionHasErrors(['type' => 'The selected type is invalid.']);
    }

    /**
     * @test
     */
    public function status_field_must_be_valid()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The selected value is invalid.']);
    }

    /**
     * @test
     */
    public function status_field_can_be_nullable()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $this->update(['value' => ''])
            ->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function priority_field_must_be_valid()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PRIORITY]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The selected value is invalid.']);
    }

    /**
     * @test
     */
    public function priority_field_can_be_nullable()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PRIORITY]);

        $this->update(['value' => ''])
            ->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function parent_ticket_must_exists()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PARENT_TICKET]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The selected value is invalid.']);
    }

    /**
     * @test
     */
    public function parent_ticket_must_exists_in_current_workspace()
    {
        $parentTicket = Ticket::factory()->create();

        $this->ticketField->update(['type' => TicketField::TYPE_PARENT_TICKET]);

        $this->update(['value' => [$parentTicket->id]])
            ->assertSessionHasErrors(['value' => 'The selected value is invalid.']);
    }

    /**
     * @test
     */
    public function progress_must_be_a_number()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PROGRESS]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The value must be a number.']);
    }

    /**
     * @test
     */
    public function assignee_id_field_must_exist()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ASSIGNEE]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The selected value is invalid.']);
    }

    /**
     * @test
     */
    public function assignee_must_be_a_member_of_the_current_workspace()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ASSIGNEE]);

        $user = User::factory()->create();

        $this->update(['value' => $user->id])
            ->assertSessionHasErrors(['value' => 'The selected value must be a member of the current workspace.']);
    }

    /**
     * @test
     */
    public function assignee_id_field_must_exist_in_current_project()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ASSIGNEE]);

        $user = User::factory([
            'workspace_id' => $this->user->workspace_id,
        ])->create();

        $this->update(['value' => $user->id])
            ->assertSessionHasErrors(['value' => 'The selected value must be a member of the current project.']);
    }

    /**
     * @test
     */
    public function progress_must_be_at_least_0()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PROGRESS]);

        $this->update(['value' => -1])
            ->assertSessionHasErrors(['value' => 'The value must be between 0 and 100.']);
    }

    /**
     * @test
     */
    public function progress_may_not_be_grated_than_100()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PROGRESS]);

        $this->update(['value' => 101])
            ->assertSessionHasErrors(['value' => 'The value must be between 0 and 100.']);
    }

    /**
     * @test
     */
    public function custom_time_field_must_be_valid_time()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_TIME]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The value does not match the format H:i:s.']);

        $this->update(['value' => '11:00:00'])
            ->assertSessionDoesntHaveErrors('value');
    }

    /**
     * @test
     */
    public function custom_date_field_must_be_valid_date()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_DATE]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The value does not match the format Y-m-d.']);

        $this->update(['value' => '2021-12-07'])
            ->assertSessionDoesntHaveErrors('value');
    }

    /**
     * @test
     */
    public function custom_short_text_field_must_be_valid_text()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_SHORT_TEXT]);

        $this->update(['value' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['value' => 'The value must not be greater than 50 characters.']);

        $this->update(['value' => 51])->assertSessionDoesntHaveErrors('value');
    }

    /**
     * @test
     */
    public function custom_numeral_field_must_be_valid_int_value()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_NUMERAL]);

        $this->update(['value' => 'text'])
            ->assertSessionHasErrors(['value' => 'The value must be an integer.']);

        $this->update(['value' => '51'])->assertSessionDoesntHaveErrors('value');
    }

    /**
     * @test
     */
    public function custom_decimal_field_must_be_valid_decimal_value()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_DECIMAL]);

        $this->update(['value' => 'text'])
            ->assertSessionHasErrors(['value' => 'The value must be a number.']);

        $this->update(['value' => '51.1'])->assertSessionDoesntHaveErrors('value');
    }

    /**
     * @test
     */
    public function custom_checkbox_field_must_be_valid_boolean_value()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_CHECKBOX]);

        $this->update(['value' => 'text'])
            ->assertSessionHasErrors(['value' => 'The value field must be true or false.']);

        $this->update(['value' => true])->assertSessionDoesntHaveErrors('value');
    }

    /**
     * @test
     */
    public function start_date_must_be_a_valid_date()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_START_DATE]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The value does not match the format Y-m-d.']);
    }

    /**
     * @test
     */
    public function due_date_must_be_a_valid_date()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_DUE_DATE]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The value does not match the format Y-m-d.']);
    }

    /**
     * @test
     * @dataProvider invalidTimeEstimate
     */
    public function time_estimate_must_have_a_valid_format($timeEstimate)
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ESTIMATE]);

        $this->update(['value' => $timeEstimate])
            ->assertSessionHasErrors(['value' => 'The value format is invalid.']);
    }

    public function invalidTimeEstimate()
    {
        return [
            ['1'],
            ['test'],
            ['123'],
        ];
    }

    /**
     * @test
     * @dataProvider validTimeEstimate
     */
    public function user_can_update_time_estimate($timeEstimate, $workingHours, $result)
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ESTIMATE]);

        $this->ticket->project->update(['working_hours' => $workingHours]);

        $this->update(['value' => $timeEstimate])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', ['time_estimate' => $result]);
    }

    public function validTimeEstimate()
    {
        return [
            ['1m', 8, 1],
            ['100m', 8, 100],
            ['1h 1m', 8, 61],
            ['1h 0m', 8, 60],
            ['1h 10m', 8, 70],
            ['1d', 8, 480],
            ['1d 1m', 8, 481],
            ['1d 1m 1h', 8, 541],
            ['1d1m1h', 8, 541],
            ['1d', 5, 300],
        ];
    }

    /**
     * @test
     */
    public function time_spent_must_be_a_valid_int()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_TIME_SPENT]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The value must be an integer.']);
    }

    /**
     * @test
     */
    public function time_spent_must_be_at_least_0()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_TIME_SPENT]);

        $this->update(['value' => -1])
            ->assertSessionHasErrors(['value' => 'The value must be at least 0.']);
    }

    /**
     * @test
     */
    public function layer_field_must_exist_in_the_database()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_LAYER]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The selected value is invalid.']);
    }

    /**
     * @test
     */
    public function layer_field_must_exist_in_the_current_workspace()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_LAYER]);

        $this->update(['value' => Layer::factory()->create()->id])
            ->assertSessionHasErrors(['value' => 'The selected value is invalid.']);
    }

    /**
     * @test
     */
    public function it_can_add_a_watcher()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_WATCHERS]);

        $this->assertDatabaseMissing('members', [
            'member_type' => Ticket::class,
            'user_id' => $this->user->id,
        ]);

        $this->update(['value' => [$this->user->id]])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('members', [
            'member_type' => Ticket::class,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     */
    public function it_can_delete_a_watcher()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_WATCHERS]);

        $this->ticket->watchers()->attach($this->user->id);

        $this->assertDatabaseHas('members', [
            'member_type' => Ticket::class,
            'user_id' => $this->user->id,
        ]);

        $this->update(['value' => []])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseMissing('members', [
            'member_type' => Ticket::class,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     */
    public function watchers_field_must_be_an_array()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_WATCHERS]);

        $this->update(['value' => 'test'])
            ->assertSessionHasErrors(['value' => 'The value must be an array.']);
    }

    /**
     * @test
     */
    public function watcher_must_be_valid()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_WATCHERS]);

        $this->update(['value' => ['123123123']])
            ->assertSessionHasErrors(['value.0' => 'The selected value.0 is invalid.']);
    }

    /**
     * @test
     */
    public function watcher_must_exists_in_current_workspace()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_WATCHERS]);

        $this->update(['value' => [User::factory()->create()->id]])
            ->assertSessionHasErrors(['value.0' => 'The value.0 is invalid.']);
    }

    /**
     * @return TestResponse
     */
    public function update($overwrites = [])
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
            ], $overwrites));
    }
}

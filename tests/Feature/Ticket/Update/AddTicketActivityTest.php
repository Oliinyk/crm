<?php

namespace Tests\Feature\Ticket\Update;

use App\Models\Layer;
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
use Tests\TestCase;

class AddTicketActivityTest extends TestCase
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

        activity()->disableLogging();

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

        activity()->enableLogging();
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_status_update()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => Ticket::STATUS_OPEN])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => Ticket::STATUS_OPEN,
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_STATUS,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_priority_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_PRIORITY]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => Ticket::PRIORITY_LOW])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => Ticket::PRIORITY_LOW,
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_PRIORITY,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_parent_ticket_id_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_PARENT_TICKET]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => $this->ticket->id])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => $this->ticket->title,
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_PARENT_TICKET,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_progress_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_PROGRESS]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => 1])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => 1,
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_PROGRESS,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_assignee_id_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_ASSIGNEE]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => $this->user->id])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => $this->user->full_name,
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_ASSIGNEE,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_custom_time_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_TIME]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => '11:11:11'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => '11:11:11',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_TIME,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_custom_date_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_DATE]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => '2020-11-01'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => '2020-11-01',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_DATE,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_custom_short_text_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_SHORT_TEXT]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => 'test'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => 'test',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_SHORT_TEXT,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_custom_long_text_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_LONG_TEXT]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => 'test'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => 'test',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_LONG_TEXT,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_custom_numeral_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_NUMERAL]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => '1'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => '1',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_NUMERAL,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_custom_decimal_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_DECIMAL]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => '1'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => '1',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_DECIMAL,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_custom_checkbox_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_CHECKBOX]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => '1'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => '1',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_CHECKBOX,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_start_date_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_START_DATE]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => '2022-11-01'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => '2022-11-01',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_START_DATE,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_due_date_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_DUE_DATE]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => '2022-11-01'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => '2022-11-01',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_DUE_DATE,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_time_estimate_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_ESTIMATE]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => '1d'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => '1d',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_ESTIMATE,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_time_spent_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_TIME_SPENT]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => '1'])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => '1',
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_TIME_SPENT,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_layer_update()
    {
        $layer = null;

        activity()->withoutLogs(function () use (&$layer) {
            $this->ticketField->update(['type' => TicketField::TYPE_LAYER]);
            $layer = Layer::factory()->create([
                'workspace_id' => $this->user->workspace_id,
                'project_id' => $this->user->projects->first()->id,
                'author_id' => $this->user->id,
            ]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => $layer->id])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => $layer->title,
            'properties->old' => null,
            'properties->type' => TicketField::TYPE_LAYER,
            'properties->name' => ''
        ]);
    }

    /**
     * @test
     */
    public function user_must_record_activity_on_watcher_update()
    {
        activity()->withoutLogs(function () {
            $this->ticketField->update(['type' => TicketField::TYPE_WATCHERS]);
        });

        $this->assertDatabaseCount(Activity::class, 0);

        $this->update(['value' => [$this->user->id]])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseCount(Activity::class, 1);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'updated',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id,
            'properties->new' => json_encode([$this->user->full_name]),
            'properties->old' => [],
            'properties->type' => TicketField::TYPE_WATCHERS,
            'properties->name' => ''
        ]);
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

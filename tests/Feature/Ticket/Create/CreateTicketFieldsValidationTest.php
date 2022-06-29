<?php

namespace Tests\Feature\Ticket\Create;

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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CreateTicketFieldsValidationTest extends TestCase
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

        $this->project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'owner_id' => $this->user->id,
            'client_id' => Client::factory()->create([
                'workspace_id' => $this->user->workspace_id,
            ]),
        ]);

        $this->project->members()->attach($this->user->id);

        $ticketType = TicketType::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'author_id' => $this->user->id,
        ]);

        $this->parentTicket = Ticket::factory([
            'workspace_id' => $this->user->workspace_id,
            'ticket_type_id' => $ticketType->id,
            'project_id' => $this->project->id,
            'author_id' => $this->user->id,
            'assignee_id' => $this->user->id,
        ])->create();

        $this->layer = Layer::factory([
            'workspace_id' => $this->user->workspace_id,
            'project_id' => $this->project->id,
            'author_id' => $this->user->id,
        ])->create();

        $this->ticketField = TicketField::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'type' => TicketField::TYPE_SHORT_TEXT,
            'name' => 'test',
            'ticket_field_type' => TicketType::class,
            'ticket_field_id' => $ticketType->id,
            'value' => null,
        ]);

        $this->user->removeRole('Administrator');
        $this->user->givePermissionTo(PermissionsEnum::CREATE_TICKETS->value);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
    }

    /**
     * @test
     */
    public function custom_field_must_be_array()
    {
        $this->create(['fields' => 'test'])
            ->assertStatus(302)
            ->assertSessionHasErrors('fields', 'The fields must be an array.');
    }

    /**
     * @test
     */
    public function type_is_required()
    {
        $this->create(['fields' => [['order' => 1]]])
            ->assertSessionHasErrors(['fields.0.type' => 'The fields.0.type field is required.']);
    }

    /**
     * @test
     */
    public function type_must_be_valid()
    {
        $this->create(['fields' => [['order' => 1, 'type' => 'test']]])
            ->assertSessionHasErrors(['fields.0.type' => 'The selected fields.0.type is invalid.']);
    }

    /**
     * @test
     */
    public function status_field_must_be_valid()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_STATUS,
                    'type' => TicketField::TYPE_STATUS,
                    'order' => 1,
                    'value' => 123,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The selected status is invalid.']);
    }

    /**
     * @test
     */
    public function status_field_can_be_nullable()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => TicketField::TYPE_STATUS,
                    'order' => 1,
                    'value' => null,
                ]
            ]
        ])->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function priority_field_must_be_valid()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PRIORITY]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_PRIORITY,
                    'type' => TicketField::TYPE_PRIORITY,
                    'order' => 1,
                    'value' => 123,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The selected priority is invalid.']);
    }

    /**
     * @test
     */
    public function priority_field_can_be_nullable()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PRIORITY]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => TicketField::TYPE_PRIORITY,
                    'order' => 1,
                    'value' => null,
                ]
            ]
        ])
            ->assertSessionHasNoErrors();
    }


    /**
     * @test
     */
    public function ticket_type_field_is_required()
    {
        $this->create(['ticket_type' => ''])
            ->assertSessionHasErrors(['ticket_type' => 'The ticket type field is required.']);
    }

    /**
     * @test
     */
    public function ticket_type_field_must_exist_in_current_workspace()
    {
        $ticketType = TicketType::factory()->create();

        $this->create(['ticket_type' => $ticketType->id])
            ->assertSessionHasErrors(['ticket_type' => 'The selected ticket type is invalid.']);
    }


    /**
     * @test
     */
    public function title_field_is_required()
    {
        $this->create(['title' => ''])
            ->assertSessionHasErrors(['title' => 'The title field is required.']);
    }

    /**
     * @test
     */
    public function title_field_max()
    {
        $this->create(['title' => Str::repeat('1', 51)])
            ->assertStatus(302)
            ->assertSessionHasErrors(['title' => 'The title must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function parent_ticket_must_exists()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PARENT_TICKET]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_PARENT_TICKET,
                    'type' => TicketField::TYPE_PARENT_TICKET,
                    'order' => 1,
                    'value' => 123,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The selected parent_ticket is invalid.']);
    }

    /**
     * @test
     */
    public function parent_ticket_must_exists_in_current_workspace()
    {
        $parentTicket = Ticket::factory()->create();

        $this->ticketField->update(['type' => TicketField::TYPE_PARENT_TICKET]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_PARENT_TICKET,
                    'type' => TicketField::TYPE_PARENT_TICKET,
                    'order' => 1,
                    'value' => $parentTicket->id,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The selected parent_ticket is invalid.']);
    }

    /**
     * @test
     */
    public function progress_must_be_a_number()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PROGRESS]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_PROGRESS,
                    'type' => TicketField::TYPE_PROGRESS,
                    'order' => 1,
                    'value' => 'test',
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The progress must be a number.']);
    }

    /**
     * @test
     */
    public function assignee_id_field_must_exist()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ASSIGNEE]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_ASSIGNEE,
                    'type' => TicketField::TYPE_ASSIGNEE,
                    'order' => 1,
                    'value' => 'test',
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The selected assignee is invalid.']);
    }

    /**
     * @test
     */
    public function assignee_id_field_must_exist_in_current_workspace()
    {
        $user = User::factory()->create();

        $this->ticketField->update(['type' => TicketField::TYPE_ASSIGNEE]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => TicketField::TYPE_ASSIGNEE,
                    'order' => 1,
                    'value' => $user->id,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The selected fields.0.value must be a member of the current project.']);
    }

    /**
     * @test
     */
    public function assignee_id_field_must_exist_in_current_project()
    {
        $user = User::factory([
            'workspace_id' => $this->user->workspace_id,
        ])->create();

        $this->ticketField->update(['type' => TicketField::TYPE_ASSIGNEE]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => TicketField::TYPE_ASSIGNEE,
                    'order' => 1,
                    'value' => $user->id,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The selected fields.0.value must be a member of the current project.']);
    }

    /**
     * @test
     */
    public function progress_must_be_at_least_0()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PROGRESS]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_PROGRESS,
                    'type' => TicketField::TYPE_PROGRESS,
                    'order' => 1,
                    'value' => -1,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The progress must be between 0 and 100.']);
    }

    /**
     * @test
     */
    public function progress_may_not_be_grated_than_100()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PROGRESS]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_PROGRESS,
                    'type' => TicketField::TYPE_PROGRESS,
                    'order' => 1,
                    'value' => 101,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The progress must be between 0 and 100.']);
    }

    /**
     * @test
     */
    public function start_date_must_be_a_valid_date()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_START_DATE]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_START_DATE,
                    'type' => TicketField::TYPE_START_DATE,
                    'order' => 1,
                    'value' => 'test',
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The start_date does not match the format Y-m-d.']);
    }

    /**
     * @test
     */
    public function due_date_must_be_a_valid_date()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_DUE_DATE]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_DUE_DATE,
                    'type' => TicketField::TYPE_DUE_DATE,
                    'order' => 1,
                    'value' => 'test',
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The due_date does not match the format Y-m-d.']);
    }

    /**
     * @test
     * @dataProvider invalidTimeEstimate
     */
    public function time_estimate_must_have_a_valid_format($timeEstimate)
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ESTIMATE]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => TicketField::TYPE_ESTIMATE,
                    'order' => 1,
                    'value' => $timeEstimate,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The fields.0.value format is invalid.']);
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
    public function time_estimate_must_have_a_valid_format1($timeEstimate, $workingHours, $result)
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ESTIMATE]);

        $this->project->update([
            'working_hours' => $workingHours,
        ]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => TicketField::TYPE_ESTIMATE,
                    'order' => 1,
                    'value' => $timeEstimate,
                ]
            ]
        ])
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

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_TIME_SPENT,
                    'type' => TicketField::TYPE_TIME_SPENT,
                    'order' => 1,
                    'value' => 'test',
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The time_spent must be an integer.']);
    }

    /**
     * @test
     */
    public function time_spent_must_be_at_least_0()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_TIME_SPENT]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_TIME_SPENT,
                    'type' => TicketField::TYPE_TIME_SPENT,
                    'order' => 1,
                    'value' => -1,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The time_spent must be at least 0.']);
    }

    /**
     * @test
     */
    public function layer_field_must_exist_in_the_database()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_LAYER]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_LAYER,
                    'type' => TicketField::TYPE_LAYER,
                    'order' => 1,
                    'value' => 'test',
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The selected layer is invalid.']);
    }

    /**
     * @test
     */
    public function layer_field_must_exist_in_the_current_workspace()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_LAYER]);

        $this->create([
            'fields' => [
                [
                    'name' => TicketField::TYPE_LAYER,
                    'type' => TicketField::TYPE_LAYER,
                    'order' => 1,
                    'value' => Layer::factory()->create()->id,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The selected layer is invalid.']);
    }

    /**
     * @test
     */
    public function custom_time_field_must_be_valid_time()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_TIME]);

        $this->create([
            'fields' => [
                ['order' => 1, 'type' => TicketField::TYPE_TIME, 'value' => 'test', 'name' => TicketField::TYPE_TIME],
            ]
        ])->assertSessionHasErrors(['fields.0.value' => 'The time does not match the format H:i:s.']);

        $this->create([
            'fields' => [
                [
                    'order' => 1, 'type' => TicketField::TYPE_TIME, 'value' => '11:00:00',
                    'name' => TicketField::TYPE_TIME
                ],
            ]
        ])->assertSessionDoesntHaveErrors('fields.0.value');
    }

    /**
     * @test
     */
    public function custom_date_field_must_be_valid_date()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_DATE]);

        $this->create([
            'fields' => [
                ['order' => 1, 'type' => TicketField::TYPE_DATE, 'value' => 'test', 'name' => 'date'],
            ]
        ])->assertSessionHasErrors(['fields.0.value' => 'The date does not match the format Y-m-d.']);

        $this->create([
            'fields' => [
                ['order' => 1, 'type' => TicketField::TYPE_DATE, 'value' => '2021-07-01', 'name' => 'date'],
            ]
        ])->assertSessionDoesntHaveErrors('fields.0.value');
    }

    /**
     * @test
     */
    public function custom_short_text_field_must_be_valid_text()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_SHORT_TEXT]);

        $this->create([
            'fields' => [
                [
                    'order' => 1,
                    'type' => TicketField::TYPE_SHORT_TEXT,
                    'value' => Str::repeat('a', 51),
                    'name' => TicketField::TYPE_SHORT_TEXT,
                ]
            ]
        ])->assertSessionHasErrors(['fields.0.value' => 'The short_text must not be greater than 50 characters.']);

        $this->create([
            'fields' => [
                ['order' => 1, 'type' => TicketField::TYPE_SHORT_TEXT, 'value' => 51, 'name' => 'short_text'],
            ]
        ])->assertSessionDoesntHaveErrors('fields.0.value');
    }

    /**
     * @test
     */
    public function custom_numeral_field_must_be_valid_int_value()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_NUMERAL]);

        $this->create([
            'fields' => [
                [
                    'order' => 1,
                    'type' => TicketField::TYPE_NUMERAL,
                    'value' => 'text',
                    'name' => TicketField::TYPE_NUMERAL,
                ]
            ]
        ])->assertSessionHasErrors(['fields.0.value' => 'The numeral must be an integer.']);

        $this->create([
            'fields' => [
                ['order' => 1, 'type' => TicketField::TYPE_NUMERAL, 'value' => '51', 'name' => 'numeral'],
            ]
        ])->assertSessionDoesntHaveErrors('fields.0.value');
    }

    /**
     * @test
     */
    public function custom_decimal_field_must_be_valid_decimal_value()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_DECIMAL]);

        $this->create([
            'fields' => [
                [
                    'order' => 1,
                    'type' => TicketField::TYPE_DECIMAL,
                    'value' => 'text',
                    'name' => TicketField::TYPE_DECIMAL,
                ]
            ]
        ])->assertSessionHasErrors(['fields.0.value' => 'The decimal must be a number.']);

        $this->create([
            'fields' => [
                ['order' => 1, 'type' => TicketField::TYPE_DECIMAL, 'value' => '51.1', 'name' => 'decimal'],
            ]
        ])->assertSessionDoesntHaveErrors('fields.0.value');
    }

    /**
     * @test
     */
    public function custom_checkbox_field_must_be_valid_boolean_value()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_CHECKBOX]);

        $this->create([
            'fields' => [
                [
                    'order' => 1,
                    'type' => TicketField::TYPE_CHECKBOX,
                    'value' => 'text',
                    'name' => TicketField::TYPE_CHECKBOX,
                ]
            ]
        ])->assertSessionHasErrors(['fields.0.value' => 'The checkbox field must be true or false.']);

        $this->create([
            'fields' => [
                ['order' => 1, 'type' => TicketField::TYPE_CHECKBOX, 'value' => true, 'name' => 'checkbox'],
            ]
        ])->assertSessionDoesntHaveErrors('fields.0.value');
    }

    /**
     * @test
     */
    public function it_can_add_a_watcher()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_WATCHERS]);

        $this->assertDatabaseMissing('members', [
            'member_type' => Ticket::class,
        ]);

        $this->create([
            'fields' => [
                [
                    'order' => 1,
                    'type' => TicketField::TYPE_WATCHERS,
                    'value' => [$this->user->id],
                    'name' => 123,
                ]
            ]
        ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('members', [
            'member_type' => Ticket::class,
            'user_id' => (string) $this->user->id,
        ]);
    }

    /**
     * @test
     */
    public function watchers_field_must_be_an_array()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_WATCHERS]);

        $this->create([
            'fields' => [
                [
                    'order' => 1,
                    'type' => TicketField::TYPE_WATCHERS,
                    'value' => 'test',
                    'name' => TicketField::TYPE_WATCHERS,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value' => 'The watchers must be an array.']);
    }

    /**
     * @test
     */
    public function watcher_must_be_valid()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_WATCHERS]);

        $this->create([
            'fields' => [
                [
                    'order' => 1,
                    'type' => TicketField::TYPE_WATCHERS,
                    'value' => [123123],
                    'name' => 123,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value.0' => 'The selected fields.0.value.0 is invalid.']);
    }

    /**
     * @test
     */
    public function watcher_must_exists_in_current_workspace()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_WATCHERS]);

        $this->create([
            'fields' => [
                [
                    'order' => 1,
                    'type' => TicketField::TYPE_WATCHERS,
                    'value' => [User::factory()->create()->id],
                    'name' => 123,
                ]
            ]
        ])
            ->assertSessionHasErrors(['fields.0.value.0' => 'The fields.0.value.0 is invalid.']);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        $data = array_merge([
            'title' => 'title',
            'ticket_type' => $this->ticketField->ticketField->id,
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => $this->ticketField->value,
                ],
            ],
        ], $overwtites);

        $route = route('ticket.store', [
            'project' => $this->project->id,
            'workspace' => $this->user->workspace_id
        ]);

        return $this->actingAs($this->user)
            ->post($route, $data);
    }
}

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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class CreateTicketTest extends TestCase
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
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->user->givePermissionTo(PermissionsEnum::CREATE_TICKETS->value);
    }

    /**
     * @test
     */
    public function user_can_create_a_ticket_with_a_media()
    {
        $this->ticketField->update([
            'type' => TicketField::TYPE_LAYER,
            'value' => [$this->layer->id],
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg');
        $file = Storage::disk('public')->putFileAs('temp/', $file, $file->getClientOriginalName());

        $response = $this->create([
            'media' => [['url' => $file]],
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $media = Ticket::whereLayerId($this->layer->id)->first()->getFirstMedia();

        Storage::disk('public')->assertExists($media->id.'/'.$media->file_name);
    }

    /**
     * @test
     */
    public function user_can_create_a_status()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_STATUS]);

        $response = $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => Ticket::STATUS_RESOLVED,
                ],
            ],
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'status' => Ticket::STATUS_RESOLVED,
        ]);
    }

    /**
     * @test
     */
    public function user_can_create_a_priority()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PRIORITY]);

        $response = $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => Ticket::PRIORITY_LOW,
                ],
            ],
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'priority' => Ticket::PRIORITY_LOW,
        ]);
    }

    /**
     * @test
     */
    public function user_can_create_a_layer_id()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_LAYER]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => [$this->layer->id],
                ],
            ],
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'layer_id' => $this->layer->id,
        ]);
    }

    /**
     * @test
     */
    public function user_can_create_a_parent_ticket_id()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PARENT_TICKET]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => [$this->parentTicket->id],
                ],
            ],
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'parent_ticket_id' => $this->parentTicket->id,
        ]);
    }

    /**
     * @test
     */
    public function user_can_create_a_parent_progress()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_PROGRESS]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => 11,
                ],
            ],
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'progress' => 11,
        ]);
    }

    /**
     * @test
     */
    public function user_can_create_a_parent_assignee_id()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ASSIGNEE]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => [$this->user->id],
                ],
            ],
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'assignee_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     */
    public function user_can_create_a_parent_start_date()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_START_DATE]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => '2020-01-01',
                ],
            ],
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'start_date' => '2020-01-01',
        ]);
    }

    /**
     * @test
     */
    public function user_can_create_a_parent_due_date()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_DUE_DATE]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => '2020-01-01',
                ],
            ],
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'due_date' => '2020-01-01',
        ]);
    }

    /**
     * @test
     */
    public function user_can_create_a_time_estimate()
    {
        $this->ticketField->update(['type' => TicketField::TYPE_ESTIMATE]);

        $this->create([
            'fields' => [
                [
                    'name' => 'test',
                    'type' => $this->ticketField->type,
                    'order' => 1,
                    'value' => '1h',
                ],
            ],
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'time_estimate' => '60',
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_a_ticket()
    {
        $this->user->revokePermissionTo(PermissionsEnum::CREATE_TICKETS->value);

        $this->create()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_must_record_activity()
    {
        $this->assertDatabaseCount(Activity::class, 1);

        $this->create();

        $this->assertDatabaseCount(Activity::class, 2);

        $this->assertDatabaseHas(Activity::class, [
            'event' => 'created',
            'subject_type' => Ticket::class,
            'causer_id' => $this->user->id
        ]);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        $data = array_merge([
            'ticket_type' => $this->ticketField->ticketField->id,
            'title' => 'title',
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

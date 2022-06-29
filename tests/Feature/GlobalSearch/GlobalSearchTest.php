<?php

namespace Tests\Feature\GlobalSearch;

use App\Enums\PermissionsEnum;
use App\Http\Resources\Search\UserResource;
use App\Models\Client;
use App\Models\Layer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    private Collection|User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);

        activity()->disableLogging();
    }

    /**
     * @test
     */
    public function it_can_find_users()
    {
        $response = $this->search(['search' => $this->user->full_name]);

        $this->assertSame($response->decodeResponseJson()->json(), [
            User::class => [
                [
                    'id' => $this->user->id,
                    'full_name' => $this->user->full_name,
                    'email' => $this->user->email,
                    'deleted_at' => null,
                    'locale' => 'en',
                    'image' => [
                        'url' => '',
                        'id' => null,
                        'thumb' => '',
                        'name' => null,
                        'size' => null,
                        'type' => null,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_can_find_clients()
    {
        $this->user->givePermissionTo(PermissionsEnum::SEE_CLIENTS->value);

        /**
         * @var Client $client
         */
        $client = Client::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $client->name]);

        $this->assertSame($response->decodeResponseJson()->json(), [
            Client::class => [
                [
                    'id' => $client->id,
                    'name' => $client->name,
                    'workspace_id' => $client->workspace_id,
                    'status' => $client->status,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'city' => $client->city,
                    'created_at' => $client->created_at->toISOString(),
                    'updated_at' => $client->updated_at->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_cant_find_clients_without_permission()
    {
        /**
         * @var Client $client
         */
        $client = Client::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $client->name]);

        $this->assertSame($response->decodeResponseJson()->json(), []);
    }

    /**
     * @test
     */
    public function it_can_find_layers()
    {
        /**
         * @var Layer $layer
         */
        $layer = Layer::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $layer->title]);

        $this->assertSame($response->decodeResponseJson()->json(), [
            Layer::class => [
                [
                    'id' => $layer->id,
                    'title' => $layer->title,
                    'workspace_id' => $layer->workspace_id,
                    'project_id' => $layer->project_id,
                    'author_id' => $layer->author_id,
                    'parent_layer_id' => null,
                    'created_at' => $layer->created_at->toISOString(),
                    'updated_at' => $layer->updated_at->toISOString(),
                    'media' => [],
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_can_find_projects()
    {
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);

        /**
         * @var Project $project
         */
        $project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $project->name]);

        $this->assertSame($response->decodeResponseJson()->json(), [
            Project::class => [
                [
                    'id' => $project->id,
                    'workspace_id' => $project->workspace_id,
                    'owner_id' => $project->owner_id,
                    'client_id' => $project->client_id,
                    'name' => $project->name,
                    'working_hours' => $project->working_hours,
                    'created_at' => $project->created_at->toISOString(),
                    'updated_at' => $project->updated_at->toISOString(),
                    'deleted_at' => null,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_can_find_joined_projects()
    {
        $this->user->givePermissionTo(PermissionsEnum::SEE_JOINED_PROJECTS->value);

        /**
         * @var Project $project
         */
        $project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $project->name]);

        $this->assertSame($response->decodeResponseJson()->json(), []);

        $project->members()->attach($this->user->id);

        $response = $this->search(['search' => $project->name]);

        $this->assertSame($response->decodeResponseJson()->json(), [
            Project::class => [
                [
                    'id' => $project->id,
                    'workspace_id' => $project->workspace_id,
                    'owner_id' => $project->owner_id,
                    'client_id' => $project->client_id,
                    'name' => $project->name,
                    'working_hours' => $project->working_hours,
                    'created_at' => $project->created_at->toISOString(),
                    'updated_at' => $project->updated_at->toISOString(),
                    'deleted_at' => null,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_cant_find_projects_without_permission()
    {
        /**
         * @var Project $project
         */
        $project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $project->name]);

        $this->assertSame($response->decodeResponseJson()->json(), []);
    }

    /**
     * @test
     */
    public function it_can_find_tickets()
    {
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);

        /**
         * @var Ticket $ticket
         */
        $ticket = Ticket::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $ticket->title]);

        $this->assertSame([
            Ticket::class => [
                [
                    'id' => $ticket->id,
                    'assignee' => (new UserResource($ticket->assignee))->toArray(request()),
                    'due_date' => (string) $ticket->due_date->toDateString(),
                    'layer' => null,
                    'parent_ticket' => null,
                    'child_tickets' => [],
                    'watchers' => [],
                    'priority' => (string) $ticket->priority,
                    'progress' => $ticket->progress,
                    'start_date' => (string) $ticket->start_date->toDateString(),
                    'status' => (string) $ticket->status,
                    'ticket_type' => null,
                    'ticket_type_id' => null,
                    'time_estimate' => (string) $ticket->time_estimate,
                    'time_spent' => $ticket->time_spent,
                    'title' => (string) $ticket->title,
                    'changed' => (string) $ticket->updated_at->toDateTimeString(),
                    'author' => [
                        'id' => $ticket->author_id,
                        'full_name' => $ticket->author->full_name,
                        'email' => $ticket->author->email,
                        'deleted_at' => null,
                        'locale' => 'en',
                        'image' => [
                            'url' => '',
                            'id' => null,
                            'thumb' => '',
                            'name' => null,
                            'size' => null,
                            'type' => null,
                        ],
                    ],
                    'fields' => [],
                    'media' => [],
                    'comments' => [],
                    'activity_log' => []
                ],
            ],
        ], $response->decodeResponseJson()->json());
    }

    /**
     * @test
     */
    public function it_can_find_joined_tickets()
    {
        $this->user->givePermissionTo(PermissionsEnum::SEE_JOINED_TICKETS->value);

        /**
         * @var Ticket $ticket
         */
        $ticket = Ticket::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $ticket->title]);

        $this->assertSame($response->decodeResponseJson()->json(), []);

        $ticket->update([
            'author_id' => $this->user->id,
        ]);

        $response = $this->search(['search' => $ticket->title]);

        $this->assertSame($response->decodeResponseJson()->json(), [
            Ticket::class => [
                [
                    'id' => $ticket->id,
                    'assignee' => (new UserResource($ticket->assignee))->toArray(request()),
                    'due_date' => (string) $ticket->due_date->toDateString(),
                    'layer' => null,
                    'parent_ticket' => null,
                    'child_tickets' => [],
                    'watchers' => [],
                    'priority' => (string) $ticket->priority,
                    'progress' => $ticket->progress,
                    'start_date' => (string) $ticket->start_date->toDateString(),
                    'status' => (string) $ticket->status,
                    'ticket_type' => null,
                    'ticket_type_id' => null,
                    'time_estimate' => (string) $ticket->time_estimate,
                    'time_spent' => $ticket->time_spent,
                    'title' => (string) $ticket->title,
                    'changed' => (string) $ticket->updated_at->toDateTimeString(),
                    'author' => [
                        'id' => $ticket->author_id,
                        'full_name' => $ticket->author->full_name,
                        'email' => $ticket->author->email,
                        'deleted_at' => null,
                        'locale' => 'en',
                        'image' => [
                            'url' => '',
                            'id' => null,
                            'thumb' => '',
                            'name' => null,
                            'size' => null,
                            'type' => null,
                        ],
                    ],
                    'fields' => [],
                    'media' => [],
                    'comments' => [],
                    'activity_log' => []
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_cant_find_tickets_without_permission()
    {
        /**
         * @var Ticket $ticket
         */
        $ticket = Ticket::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $ticket->title]);

        $this->assertSame($response->decodeResponseJson()->json(), []);
    }

    /**
     * @test
     */
    public function it_can_find_ticket_types()
    {
        $this->user->givePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);

        /**
         * @var TicketType $ticketType
         */
        $ticketType = TicketType::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $ticketType->title]);

        $this->assertSame($response->decodeResponseJson()->json(), [
            TicketType::class => [
                [
                    'id' => $ticketType->id,
                    'name' => $ticketType->name,
                    'title' => $ticketType->title,
                    'updated_at' => $ticketType->updated_at->toDateString(),
                    'deleted_at' => $ticketType->deleted_at,
                    'fields' => [],
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_cant_find_ticket_types_without_permission()
    {
        /**
         * @var TicketType $ticketType
         */
        $ticketType = TicketType::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $response = $this->search(['search' => $ticketType->title]);

        $this->assertSame($response->decodeResponseJson()->json(), []);
    }

    public function search($overwrites = [])
    {
        $route = route('global.search', array_merge([
            'workspace' => $this->user->workspace_id,
            'search' => 'test'
        ], $overwrites));

        return $this->actingAs($this->user)->get($route);
    }
}

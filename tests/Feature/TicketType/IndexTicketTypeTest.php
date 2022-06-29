<?php

namespace Tests\Feature\TicketType;

use App\Enums\PermissionsEnum;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class IndexTicketTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var TicketType
     */
    public $ticketType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->ticketType = TicketType::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);
        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->givePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * @test
     */
    public function user_can_see_ticket_types()
    {
        $this->index()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketTypes/Index')
                ->has('ticketTypes.data', 1)
                ->has('ticketTypes.data.0', fn (Assert $page) => $page
                    ->where('id', $this->ticketType->id)
                    ->where('name', $this->ticketType->name)
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_can_search_ticket_types_by_name()
    {
        TicketType::factory()->count(10)->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->ticketType->update([
            'name' => 'lol kek',
        ]);

        $this->index(['search' => 'lol kek'])
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketTypes/Index')
                ->has('ticketTypes.data', 1)
                ->has('ticketTypes.data.0', fn (Assert $page) => $page
                    ->where('id', $this->ticketType->id)
                    ->where('name', $this->ticketType->name)
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_can_search_ticket_types_by_title()
    {
        TicketType::factory()->count(10)->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->ticketType->update([
            'title' => 'lol kek',
        ]);

        $this->index(['search' => 'lol kek'])
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketTypes/Index')
                ->has('ticketTypes.data', 1)
                ->has('ticketTypes.data.0', fn (Assert $page) => $page
                    ->where('id', $this->ticketType->id)
                    ->where('name', $this->ticketType->name)
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_see_ticket_types()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);

        $this->index()->assertStatus(403);
    }

    /**
     * @return TestResponse
     */
    public function index($overwrites = [])
    {
        return $this->actingAs($this->user)
            ->get(route('ticket-type.index', array_merge([
                'workspace' => $this->user->workspace_id
            ], $overwrites)));
    }
}

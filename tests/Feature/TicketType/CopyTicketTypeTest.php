<?php

namespace Tests\Feature\TicketType;

use App\Enums\PermissionsEnum;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CopyTicketTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Collection|Model
     */
    public $ticketType;

    protected function setUp(): void
    {
        parent::setUp();


        $this->user = User::factory()->create();

        $this->ticketType = TicketType::factory([
            'workspace_id' => $this->user->workspace_id,
            'author_id' => $this->user->id,
        ])->create();

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->givePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * @test
     */
    public function user_can_copy_ticket_type()
    {
        /**
         * The Ticket field of the Ticket type.
         */
        TicketField::factory([
            'workspace_id' => $this->user->workspace_id,
            'ticket_field_type' => TicketType::class,
            'ticket_field_id' => $this->ticketType->id,
        ])->create();

        /**
         * Check if the database has only one ticket type, and ticket field.
         */
        $this->assertDatabaseCount('ticket_types', 1);
        $this->assertDatabaseCount('ticket_fields', 1);

        /**
         * Copy ticket type and all ticket fields.
         */
        $this->copy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        /**
         * Database must have 2 ticket types, and ticket fields.
         */
        $this->assertDatabaseCount('ticket_types', 2);
        $this->assertDatabaseCount('ticket_fields', 2);

        /**
         * Check if the database has new ticket types.
         */
        $this->assertDatabaseHas('ticket_types', [
            'name' => $this->ticketType->name.' - copy',
            'title' => $this->ticketType->title,
            'workspace_id' => $this->user->workspace_id,
            'author_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_copy_a_ticket_type()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);

        $this->copy()->assertStatus(403);
    }

    /**
     * @return TestResponse
     */
    public function copy($overwtites = [])
    {
        $route = route('ticket-type.copy', [
            'ticketType' => $this->ticketType,
            'workspace' => $this->user->workspace_id
        ]);

        return $this->actingAs($this->user)
            ->post($route, array_merge([
                'name' => 'test',
                'title' => 'test1',
            ], $overwtites));
    }
}

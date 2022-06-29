<?php

namespace Tests\Feature\TicketType;

use App\Enums\PermissionsEnum;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ForceDeleteTicketTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var TicketType|Collection|Model
     */
    public $ticketType;

    /**
     * @var Workspace|Collection|Model
     */
    public $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();

        $this->workspace = $this->user->workspaces()->first();

        $this->ticketType = TicketType::factory()->create([
            'workspace_id' => $this->workspace->id,
            'author_id' => $this->user->id,
        ]);
        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * @test
     */
    public function user_can_force_delete_ticket_type()
    {
        $this->assertDatabaseHas('ticket_types', [
            'id' => $this->ticketType->id,
        ]);

        $this->forceDestroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('ticket_types', [
            'id' => $this->ticketType->id,
        ]);
    }

    /**
     * @test
     */
    public function it_must_delete_ticket_fields()
    {
        TicketField::factory([
            'workspace_id' => $this->user->workspace_id,
            'ticket_field_type' => TicketType::class,
            'ticket_field_id' => $this->ticketType->id,
        ])->create();

        $this->forceDestroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('ticket_fields', 0);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_force_delete_ticket_type()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);

        $this->forceDestroy()->assertStatus(403);
    }

    /**
     * @return TestResponse
     */
    public function forceDestroy()
    {
        return $this->actingAs($this->user)
            ->delete(route('ticket-type.destroy.force', [
                'ticketType' => $this->ticketType,
                'workspace' => $this->user->workspace_id
            ]));
    }
}

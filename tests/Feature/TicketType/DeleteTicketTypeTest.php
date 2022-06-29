<?php

namespace Tests\Feature\TicketType;

use App\Enums\PermissionsEnum;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DeleteTicketTypeTest extends TestCase
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
    public function user_can_delete_ticket_type()
    {
        Carbon::setTestNow();

        $this->assertDatabaseHas('ticket_types', [
            'id' => $this->ticketType->id,
            'deleted_at' => null,
        ]);

        $this->destroy()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ticket_types', [
            'id' => $this->ticketType->id,
            'deleted_at' => now(),
        ]);
    }

    /**
     * @test
     */
    public function user_can_delete_multiple_ticket_types()
    {
        Carbon::setTestNow();

        $ticketType1 = TicketType::factory()->create([
            'workspace_id' => $this->workspace->id,
            'author_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('ticket_types', [
            'id' => $this->ticketType->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType1->id,
            'deleted_at' => null,
        ]);

        $this->destroy(['ids' => [$this->ticketType->id, $ticketType1->id]])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ticket_types', [
            'id' => $this->ticketType->id,
            'deleted_at' => now(),
        ]);

        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType1->id,
            'deleted_at' => now(),
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_delete_ticket_type()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);

        $this->destroy()->assertStatus(403);
    }

    /**
     * @return TestResponse
     */
    public function destroy($overwrites = [])
    {
        return $this->actingAs($this->user)
            ->delete(
                route('ticket-type.destroy', [
                    'workspace' => $this->user->workspace_id
                ]),
                array_merge(['ids' => [$this->ticketType->id]], $overwrites)
            );
    }
}

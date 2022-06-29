<?php

namespace Tests\Feature\TicketType\Update;

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

class UpdateTicketTypeFieldsTest extends TestCase
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
    public function user_can_update_ticket_type_fields()
    {
        $this->update()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ticket_fields', [
            'workspace_id' => $this->user->workspace_id,
            'order' => 1,
            'type' => TicketField::TYPE_STATUS,
            'name' => 'test status',
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'workspace_id' => $this->user->workspace_id,
            'order' => 2,
            'type' => TicketField::TYPE_DATE,
            'name' => 'test date',
        ]);
    }

    /**
     * @test
     */
    public function it_must_save_ticket_type_without_fields()
    {
        $this->markTestSkipped('Will be known if we need this in the future');

        $this->update([
            'name' => 'test',
            'title' => 'test',
            'fields' => [],
        ])->assertSessionHasNoErrors();
    }

    /**
     * @return TestResponse
     */
    public function update($overwtites = [])
    {
        $route = route('ticket-type.update', [
            'ticketType' => $this->ticketType,
            'workspace' => $this->user->workspace_id
        ]);

        return $this->actingAs($this->user)
            ->put($route, array_merge([
                'name' => 'test',
                'title' => 'test1',
                'fields' => [
                    [
                        'order' => 1,
                        'type' => TicketField::TYPE_STATUS,
                        'name' => 'test status',
                    ],
                    [
                        'order' => 2,
                        'type' => TicketField::TYPE_DATE,
                        'name' => 'test date',
                    ],
                ],
            ], $overwtites));
    }
}

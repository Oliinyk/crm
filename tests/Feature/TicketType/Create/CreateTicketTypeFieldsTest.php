<?php

namespace Tests\Feature\TicketType\Create;

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

class CreateTicketTypeFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->givePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * @test
     */
    public function user_can_create_ticket_type_fields()
    {
        $this->create()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ticket_fields', [
            'workspace_id' => $this->user->workspace_id,
            'order' => 1,
            'type' => TicketField::TYPE_STATUS,
            'name' => 'test status',
            'ticket_field_type' => TicketType::class,
        ]);

        $this->assertDatabaseHas('ticket_fields', [
            'workspace_id' => $this->user->workspace_id,
            'order' => 2,
            'type' => TicketField::TYPE_DATE,
            'name' => 'date field',
            'ticket_field_type' => TicketType::class,
        ]);
    }

    /**
     * @test
     */
    public function it_must_create_ticket_type_without_fields()
    {
        $this->markTestSkipped('Will be known if we need this in the future');

        $this->create([
            'name' => 'test',
            'title' => 'test',
            'fields' => [],
        ])->assertStatus(302)
            ->assertSessionHasNoErrors();
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        return $this->actingAs($this->user)
            ->post(route('ticket-type.store', $this->user->workspace_id), array_merge([
                'name' => 'test',
                'title' => 'test',
                'fields' => [
                    [
                        'order' => 1,
                        'type' => TicketField::TYPE_STATUS,
                        'name' => 'test status',
                    ],
                    [
                        'order' => 2,
                        'type' => TicketField::TYPE_DATE,
                        'name' => 'date field',
                    ],
                ],
            ], $overwtites));
    }
}

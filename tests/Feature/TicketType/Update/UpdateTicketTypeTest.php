<?php

namespace Tests\Feature\TicketType\Update;

use App\Enums\PermissionsEnum;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UpdateTicketTypeTest extends TestCase
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
    public function user_can_update_ticket_type()
    {
        $this->update()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ticket_types', [
            'name' => 'test',
            'title' => 'test1',
            'workspace_id' => $this->user->workspace_id,
            'author_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_update_a_ticket_type()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);

        $this->update()->assertStatus(403);
    }

    /**
     * @test
     */
    public function the_name_field_is_required()
    {
        $this->update(['name' => ''])
            ->assertSessionHasErrors(['name' => 'The name field is required.']);
    }

    /**
     * @test
     */
    public function the_name_may_not_be_greater_than_50_characters()
    {
        $this->update(['name' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['name' => 'The name must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_name_field_should_be_unique()
    {
        /**
         * Ignore ticket types from other workspaces.
         */
        TicketType::factory()->create(['name' => 'test']);

        /**
         * Ignore current name
         */
        $this->update(['name' => 'test'])
            ->assertSessionHasNoErrors();

        $this->update(['name' => 'test'])
            ->assertSessionHasNoErrors();

        TicketType::factory([
            'workspace_id' => $this->user->workspace_id,
            'author_id' => $this->user->id,
            'name' => 'test',
        ])->create();

        $this->update(['name' => 'test'])
            ->assertSessionHasErrors(['name' => 'The name has already been taken.']);
    }

    /**
     * @test
     */
    public function the_title_field_is_required()
    {
        $this->update(['title' => ''])
            ->assertSessionHasErrors(['title' => 'The title field is required.']);
    }

    /**
     * @test
     */
    public function the_title_may_not_be_greater_than_50_characters()
    {
        $this->update(['title' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['title' => 'The title must not be greater than 50 characters.']);
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
                        'group' => 1,
                        'order' => 1,
                        'type' => TicketField::TYPE_STATUS,
                        'name' => 'test status',
                    ],
                ],
            ], $overwtites));
    }
}

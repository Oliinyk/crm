<?php

namespace Tests\Feature\TicketType\Create;

use App\Enums\PermissionsEnum;
use App\Models\TicketField;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CreateTicketTypeFieldsValidationTest extends TestCase
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

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);

        $this->user->givePermissionTo(PermissionsEnum::MANAGE_TICKET_TYPES->value);
    }

    /**
     * @test
     */
    public function fields_must_be_an_array()
    {
        $this->create(['fields' => 'test'])
            ->assertSessionHasErrors(['fields' => 'The fields must be an array.']);
    }

    /**
     * @test
     */
    public function fields_must_be_at_list_1_items()
    {
        $this->create(['fields' => []])
            ->assertSessionHasErrors(['fields' => 'The fields must have at least 1 items.']);
    }

    /**
     * @test
     */
    public function fields_must_have_a_valid_array_keys()
    {
        $this->create(['fields' => ['test']])
            ->assertSessionHasErrors([
                'fields.0.type' => 'The fields.0.type field is required.',
                'fields.0.name' => 'The fields.0.name field is required unless fields.0.type is in '.TicketField::TYPE_SEPARATOR.'.',
            ]);
    }

    /**
     * @test
     */
    public function fields_must_exclude_unvalidated_array_keys()
    {
        $this->create([
            'fields' => [
                [
                    'group' => 'TEST', 'order' => 1, 'type' => TicketField::TYPE_CHECKBOX, 'name' => 'test status'
                ]
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();
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
    public function name_is_required()
    {
        $this->create(['fields' => [['order' => 1]]])
            ->assertSessionHasErrors(['fields.0.name' => 'The fields.0.name field is required unless fields.0.type is in '.TicketField::TYPE_SEPARATOR.'.']);
    }

    /**
     * @test
     */
    public function name_may_not_be_greater_than_50_characters()
    {
        $this->create([
            'fields' => [
                ['order' => 1, 'type' => TicketField::TYPE_CHECKBOX, 'name' => Str::repeat('a', 51)],
            ]
        ])->assertSessionHasErrors([
            'fields.0.name' => 'The fields.0.name must not be greater than 50 characters.',
        ]);
    }

    /**
     * @test
     */
    public function default_fields_type_must_not_have_any_duplicate_values()
    {
        $this->create([
            'fields' => [
                ['order' => 1, 'type' => TicketField::TYPE_STATUS],
                ['order' => 2, 'type' => TicketField::TYPE_STATUS],
            ]
        ])->assertSessionHasErrors([
            'fields.0.type' => 'The fields.0.type field has a duplicate value.',
            'fields.1.type' => 'The fields.1.type field has a duplicate value.',
        ]);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        return $this->actingAs($this->user)
            ->post(route('ticket-type.store', [
                'workspace' => $this->user->workspace_id
            ]), array_merge([
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

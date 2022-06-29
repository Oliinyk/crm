<?php

namespace Tests\Feature\Group;

use App\Enums\PermissionsEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CreateGroupTest extends TestCase
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

        $this->user->givePermissionTo(PermissionsEnum::MANAGE_GROUPS->value);
    }

    /**
     * @test
     */
    public function user_can_create_group()
    {
        $this->create()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('groups', [
            'name' => 'test',
            'workspace_id' => $this->user->workspace_id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_a_group()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_GROUPS->value);

        $this->create()->assertStatus(403);
    }

    /**
     * @test
     */
    public function the_name_field_is_required()
    {
        $this->create(['name' => ''])
            ->assertSessionHasErrors(['name' => 'The name field is required.']);
    }

    /**
     * @test
     */
    public function the_name_field_must_be_unique_in_current_workspace()
    {
        $this->create(['name' => 'test'])
            ->assertSessionDoesntHaveErrors();

        $this->create(['name' => 'test'])
            ->assertSessionHasErrors(['name' => 'The name has already been taken.']);
    }

    /**
     * @test
     */
    public function the_name_may_not_be_greater_than_50_characters()
    {
        $this->create(['name' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['name' => 'The name must not be greater than 50 characters.']);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        return $this->actingAs($this->user)
            ->post(route('group.store', array_merge([
                'workspace' => $this->user->workspace_id,
                'name' => 'test',
            ], $overwtites)));
    }
}

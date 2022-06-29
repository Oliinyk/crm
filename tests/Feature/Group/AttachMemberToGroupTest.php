<?php

namespace Tests\Feature\Group;

use App\Enums\PermissionsEnum;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttachMemberToGroupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Group
     */
    public $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->group = Group::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);

        $this->user->givePermissionTo(PermissionsEnum::MANAGE_GROUPS->value);
    }

    /**
     * @test
     */
    public function user_can_attach_members_to_group()
    {
        /**
         * @var User $anotherUser
         */
        $anotherUser = User::factory()->create([]);
        $this->user->workspaces()->first()->members()->attach($anotherUser->id);

        $this->attach(['members' => [$anotherUser->id]]);

        $this->assertDatabaseHas('members', [
            'user_id' => $anotherUser->id,
            'member_type' => Group::class,
            'member_id' => $this->group->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_a_group()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_GROUPS->value);

        $this->attach()->assertStatus(403);
    }

    /**
     * @test
     */
    public function members_field_is_required()
    {
        $this->attach(['members' => ''])
            ->assertSessionHasErrors(['members' => 'The members field is required.']);
    }

    /**
     * @test
     */
    public function members_field_must_be_an_array()
    {
        $this->attach(['members' => ''])
            ->assertSessionHasErrors(['members' => 'The members must be an array.']);
    }

    /**
     * @test
     */
    public function members_must_exist_in_a_database()
    {
        $this->attach(['members' => [123]])
            ->assertSessionHasErrors(['members.0' => 'The selected member is invalid.']);
    }

    /**
     * @test
     */
    public function members_must_exist_in_a_current_workspace()
    {
        $user1 = User::factory()->create();

        $this->attach(['members' => [$user1->id]])
            ->assertSessionHasErrors(['members.0' => 'The selected member is invalid.']);
    }

    /**
     * @return TestResponse
     */
    public function attach($overwtites = [])
    {
        $route = route('group.member.store', [
            'workspace' => $this->user->workspace_id,
            'group' => $this->group
        ]);

        return $this->actingAs($this->user)
            ->post($route, array_merge([
                'members' => [$this->user->id],
            ], $overwtites));
    }
}

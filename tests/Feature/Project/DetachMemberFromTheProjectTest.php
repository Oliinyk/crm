<?php

namespace Tests\Feature\Project;

use App\Enums\PermissionsEnum;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DetachMemberFromTheProjectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Project
     */
    public $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);

        $this->user->givePermissionTo(PermissionsEnum::MANAGE_PROJECT_MEMBERS->value);
    }

    /**
     * @test
     */
    public function user_can_detach_members_from_a_project()
    {
        /**
         * @var User $anotherUser
         */
        $anotherUser = User::factory()->create();

        $anotherUser->workspaces()->attach($this->user->workspace_id);
        $this->project->members()->attach($anotherUser->id);

        $this->detach(['members' => [$anotherUser->id]])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $this->assertDatabaseMissing('members', [
            'user_id' => $anotherUser->id,
            'member_type' => Project::class,
            'member_id' => $this->project->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_detach()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_PROJECT_MEMBERS->value);

        $this->detach()->assertStatus(403);
    }

    /**
     * @test
     */
    public function members_field_is_required()
    {
        $this->detach(['members' => ''])
            ->assertSessionHasErrors(['members' => 'The members field is required.']);
    }

    /**
     * @test
     */
    public function members_field_must_be_an_array()
    {
        $this->detach(['members' => ''])
            ->assertSessionHasErrors(['members' => 'The members must be an array.']);
    }

    /**
     * @test
     */
    public function members_must_exist_in_a_database()
    {
        $this->detach(['members' => [123]])
            ->assertSessionHasErrors(['members.0' => 'The selected member is invalid.']);
    }

    /**
     * @test
     */
    public function members_must_exist_in_a_current_workspace()
    {
        $user1 = User::factory()->create();

        $this->detach(['members' => [$user1->id]])
            ->assertSessionHasErrors(['members.0' => 'The selected member is invalid.']);
    }

    /**
     * @return TestResponse
     */
    public function detach($overwtites = [])
    {
        $route = route('project.member.destroy', [
            'workspace' => $this->user->workspace_id,
            'project' => $this->project
        ]);

        return $this->actingAs($this->user)
            ->delete($route, array_merge([
                'members' => [$this->user->id],
            ], $overwtites));
    }
}

<?php

namespace Tests\Feature\Project;

use App\Enums\PermissionsEnum;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttachMemberToTheProjectTest extends TestCase
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
    public function user_can_attach_members_to_the_project()
    {
        /**
         * @var User $anotherUser
         */
        $anotherUser = User::factory()->create([]);
        $this->user->workspaces()->first()->members()->attach($anotherUser->id);

        $this->attach(['members' => [$anotherUser->id]])
            ->assertStatus(302)
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('members', [
            'user_id' => $anotherUser->id,
            'member_type' => Project::class,
            'member_id' => $this->project->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_a_group()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_PROJECT_MEMBERS->value);

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
        $route = route('project.member.store', [
            'workspace' => $this->user->workspace_id,
            'project' => $this->project
        ]);

        return $this->actingAs($this->user)
            ->post($route, array_merge([
                'members' => [$this->user->id],
            ], $overwtites));
    }
}

<?php

namespace Tests\Unit;

use App\Enums\TimeEntryTypeEnum;
use App\Models\Group;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_belongs_to_many_own_workspaces()
    {
        User::unsetEventDispatcher();

        $user = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $user->id,
        ]);

        $this->assertInstanceOf(Workspace::class, $user->ownWorkspaces->first());
        $this->assertSame($workspace->id, $user->ownWorkspaces->first()->id);
    }

    /**
     * @test
     */
    public function user_is_a_member_of_many_workspaces()
    {
        User::unsetEventDispatcher();

        $user = User::factory()->create();

        $workspace = Workspace::factory()->create();

        $workspace->members()->attach($user);

        $this->assertInstanceOf(Workspace::class, $user->workspaces->first());
        $this->assertSame($workspace->id, $user->workspaces->first()->id);
    }

    /**
     * @test
     */
    public function user_is_a_member_of_many_projects_in_current_workspace()
    {
        $user = User::factory()->create(['id' => 123]);

        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($user->id);
        $user->update(['workspace_id' => $workspace->id]);

        auth()->login($user);
        request()->merge(['workspace' => $workspace]);

        
        $project = Project::factory()->create(['workspace_id' => $workspace->id]);
        $project->members()->attach($user->id);

        $project1 = Project::factory()->create();
        $project1->members()->attach($user->id);

        $this->assertSame(1, $user->projects->count());
    }

    /**
     * @test
     */
    public function user_belongs_to_many_own_projects()
    {

        $user = User::factory()->create();
        $workspace = $user->workspaces->first();

        auth()->login($user);
        request()->merge(['workspace' => $workspace]);

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $user->id,
        ]);

        $projectFromAnotherWorkspace = Project::factory()->create(['owner_id' => $user->id]);

        $this->assertInstanceOf(Project::class, $user->ownProjects->first());
        $this->assertSame($project->id, $user->ownProjects->first()->id);
        $this->assertSame(1, $user->ownProjects->count());
    }

    /**
     * @test
     */
    public function user_is_a_member_of_many_groups()
    {
        User::unsetEventDispatcher();

        $user = User::factory()->create();

        $group = Group::factory()->create();

        $group->members()->attach($user);

        $this->assertInstanceOf(Group::class, $user->groups->first());
        $this->assertSame($group->id, $user->groups->first()->id);
    }

    /**
     * @test
     */
    public function user_has_many_invitations()
    {
        $user = User::factory()->create();

        Invitation::factory()->create([
            'email' => $user->email,
        ]);

        $this->assertInstanceOf(Invitation::class, $user->invitations->first());
    }

    /**
     * @test
     */
    public function user_is_a_watcher_of_many_tickets()
    {
        User::unsetEventDispatcher();

        $user = User::factory()->create();

        $ticket = Ticket::factory()->create();

        $ticket->watchers()->attach($user);

        $this->assertInstanceOf(Ticket::class, $user->watchedTickets->first());
        $this->assertSame($ticket->id, $user->watchedTickets->first()->id);
    }
}

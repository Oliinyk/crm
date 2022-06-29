<?php

namespace Tests\Unit;

use App\Models\Invitation;
use App\Models\Project;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function workspace_belongs_to_owner()
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create();

        $workspace->owner()->associate($user);

        $this->assertInstanceOf(User::class, $workspace->owner);
        $this->assertSame($user->id, $workspace->owner->id);
    }

    /**
     * @test
     */
    public function workspace_has_many_members()
    {
        User::withoutEvents(function () {
            $workspace = Workspace::factory()->create();
            $user = User::factory()->create();

            $workspace->members()->attach($user);

            $this->assertInstanceOf(User::class, $workspace->members->first());
            $this->assertSame($user->id, $workspace->members->first()->id);
        });
    }

    /**
     * @test
     */
    public function workspace_has_many_projects()
    {
        $workspace = Workspace::factory()->create();

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $this->assertInstanceOf(Project::class, $workspace->projects->first());
        $this->assertSame($project->id, $workspace->projects->first()->id);
    }

    /**
     * @test
     */
    public function workspace_has_many_ticket_types()
    {
        $workspace = Workspace::factory()->create();

        $ticketType = TicketType::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $this->assertInstanceOf(TicketType::class, $workspace->ticketTypes->first());
        $this->assertSame($ticketType->id, $workspace->ticketTypes->first()->id);
    }

    /**
     * @test
     */
    public function workspace_has_many_invitations()
    {
        $workspace = Workspace::factory()->create();

        Invitation::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $this->assertInstanceOf(Invitation::class, $workspace->invitations->first());
    }
}

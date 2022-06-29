<?php

namespace Tests\Unit;

use App\Models\Layer;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function project_belongs_to_workspace()
    {
        $project = Project::factory()->create();

        $this->assertInstanceOf(Workspace::class, $project->workspace);
    }

    /**
     * @test
     */
    public function project_belongs_to_owner()
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();

        $project->owner()->associate($user);

        $this->assertInstanceOf(User::class, $project->owner);
        $this->assertSame($user->id, $project->owner->id);
    }

    /**
     * @test
     */
    public function project_has_many_members()
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();

        $project->members()->attach($user);

        $this->assertInstanceOf(User::class, $project->members->first());
        $this->assertSame($user->id, $project->members->first()->id);
    }

    /**
     * @test
     */
    public function project_has_many_layers()
    {
        $project = Project::factory()->create();
        $layer = Layer::factory()->create([
            'project_id' => $project->id,
        ]);

        $this->assertInstanceOf(Layer::class, $project->layers->first());
        $this->assertSame($layer->id, $project->layers->first()->id);
    }

    /**
     * @test
     */
    public function project_must_have_a_filter_scope()
    {
        Project::factory()->count(10)->create();

        $ticket = Project::factory()->create([
            'name' => '123123kek',
        ]);

        $result = Project::filter(['search' => '123123kek'])->get();

        $this->assertCount(1, $result);
        $this->assertSame($ticket->id, $result->first()->id);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Layer;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function layer_belongs_to_workspace()
    {
        $layer = Layer::factory()->create();

        $this->assertInstanceOf(Workspace::class, $layer->workspace);
    }

    /**
     * @test
     */
    public function layer_belongs_to_the_project()
    {
        $layer = Layer::factory()->create();

        $this->assertInstanceOf(Project::class, $layer->project);
    }

    /**
     * @test
     */
    public function layer_belongs_to_the_author()
    {
        $layer = Layer::factory()->create();

        $this->assertInstanceOf(User::class, $layer->author);
    }

    /**
     * @test
     */
    public function it_must_belongs_to_the_parent_layer()
    {
        $layer = Layer::factory()->create();
        $parentLayer = Layer::factory()->create(['workspace_id' => $layer->workspace_id]);

        $layer->update([
            'parent_layer_id' => $parentLayer->id,
        ]);

        $this->assertInstanceOf(Layer::class, $layer->parentLayer);
    }

    /**
     * @test
     */
    public function it_must_have_a_filter_scope()
    {
        Layer::factory()->count(10)->create();

        $layer = Layer::factory()->create([
            'title' => '123123kek',
        ]);

        $result = Layer::filter(['search' => '123123kek'])->get();

        $this->assertCount(1, $result);
        $this->assertSame($layer->id, $result->first()->id);
    }
}

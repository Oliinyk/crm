<?php

namespace Database\Seeders;

use App\Models\Layer;
use App\Models\Project;
use Illuminate\Database\Seeder;

class LayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Project::all()->each(function (Project $project) {
            Layer::factory()->count(env('SEEDER_LAYER', 5))->create([
                'project_id' => $project->id,
                'workspace_id' => $project->workspace_id,
                'author_id' => $project->owner_id,
            ]);
        });
    }
}

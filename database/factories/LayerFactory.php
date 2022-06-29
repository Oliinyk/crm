<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class LayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word(),
            'workspace_id' => Workspace::factory()->lazy(),
            'project_id' => Project::factory()->lazy(),
            'author_id' => User::factory()->lazy(),
        ];
    }
}

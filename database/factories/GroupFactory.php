<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'Group - '.$this->faker->word(),
            'workspace_id' => Workspace::factory()->lazy(),
        ];
    }
}

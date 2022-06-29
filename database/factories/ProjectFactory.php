<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'Project - '.$this->faker->city(),
            'workspace_id' => Workspace::factory()->lazy(),
            'client_id' => Client::factory()->lazy(),
            'owner_id' => User::factory()->lazy(),
            'working_hours' => $this->faker->randomElement([6, 7, 8]),
        ];
    }
}

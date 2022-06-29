<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'title' => $this->faker->word(),
            'workspace_id' => Workspace::factory()->lazy(),
            'author_id' => User::factory()->lazy(),
        ];
    }
}

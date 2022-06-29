<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'workspace_id' => Workspace::factory()->lazy(),
            'status' => $this->faker->randomElement(['ongoing', 'estimate', 'not started', 'finished']),
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'city' => $this->faker->city(),
        ];
    }
}

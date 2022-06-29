<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'workspace_id' => Workspace::factory()->lazy(),
            'author_id' => User::factory()->lazy(),
            'ticket_id' => Ticket::factory()->lazy(),
            'text' => $this->faker->text()
        ];
    }
}

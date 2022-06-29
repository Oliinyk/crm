<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'workspace_id' => Workspace::factory()->lazy(),
            'ticket_type_id' => TicketType::factory()->lazy(),
            'project_id' => Project::factory()->lazy(),
            'author_id' => User::factory()->lazy(),
            'title' => 'Ticket - '.$this->faker->bs(),
            'status' => $this->faker->randomElement([
                Ticket::STATUS_OPEN,
                Ticket::STATUS_IN_PROGRESS,
                Ticket::STATUS_RESOLVED,
            ]),
            'priority' => $this->faker->randomElement([
                Ticket::PRIORITY_HIGH,
                Ticket::PRIORITY_MEDIUM,
                Ticket::PRIORITY_LOW,
            ]),
            'layer_id' => null,
            'parent_ticket_id' => null,
            'progress' => $this->faker->numberBetween(1, 100),
            'assignee_id' => User::factory()->lazy(),
            'start_date' => $this->faker->dateTime(),
            'due_date' => $this->faker->dateTime(),
            'time_estimate' => $this->faker->numberBetween(1, 100).'m',
            'time_spent' => $this->faker->numberBetween(1, 100).'m',
        ];
    }
}

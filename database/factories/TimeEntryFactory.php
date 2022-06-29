<?php

namespace Database\Factories;

use App\Enums\TimeEntryTypeEnum;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class TimeEntryFactory extends Factory
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
            'ticket_id' => Ticket::factory()->lazy(),
            'author_id' => User::factory()->lazy(),
            'type' => TimeEntryTypeEnum::SPENT->value,
            'description' => $this->faker->text,
            'time' => '1m',
        ];
    }
}

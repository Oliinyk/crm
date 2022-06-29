<?php

namespace Database\Factories;

use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFieldFactory extends Factory
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
            'type' => $this->faker->randomElement(TicketField::ticketTypes()),
            'name' => $this->faker->word(),
            'order' => 1,
            'ticket_field_type' => TicketType::class,
            'ticket_field_id' => TicketType::factory()->lazy(),
            'value' => $this->faker->word(),
            'date_value' => $this->faker->date(),
            'time_value' => $this->faker->time(),
            'string_value' => $this->faker->word(),
            'text_value' => $this->faker->text(),
            'int_value' => $this->faker->numberBetween(),
            'decimal_value' => $this->faker->randomFloat(2, 0, 10000),
            'boolean_value' => $this->faker->boolean(),
        ];
    }
}

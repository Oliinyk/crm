<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketField;
use Illuminate\Database\Seeder;

class CustomFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Ticket::all()->each(function (Ticket $ticket) {
            TicketField::factory()->count(env('SEEDER_TICKET_FIELD', 2))->create([
                'workspace_id' => $ticket->workspace_id,
                'ticket_field_type' => Ticket::class,
                'ticket_field_id' => $ticket->id,
            ]);
        });
    }
}

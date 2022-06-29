<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Project::all()->each(function (Project $project) {
            Ticket::factory()->count(env('SEEDER_TICKET', 5))->create([
                'project_id' => $project->id,
                'ticket_type_id' => $project->workspace->ticketTypes()->inRandomOrder()->first()->id,
                'workspace_id' => $project->workspace_id,
                'author_id' => $project->owner_id,
            ]);
        });
    }
}

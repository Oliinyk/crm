<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Layer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketTestingSeeder extends Seeder
{
    public $user;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->user = User::factory()->create([
            'email'=>'johndoe@example.com',
            'full_name' => 'John Doe'
        ]);


        $project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'owner_id' => $this->user->id,
            'client_id' => Client::factory()->create([
                'workspace_id' => $this->user->workspace_id,
            ]),
        ]);

        $project->members()->attach($this->user->id);

        $ticketType = TicketType::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'author_id' => $this->user->id,
            'name' => 'New Ticket type'
        ]);

        $this->ticket = Ticket::factory([
            'workspace_id' => $this->user->workspace_id,
            'ticket_type_id' => $ticketType->id,
            'project_id' => $project->id,
            'author_id' => $this->user->id,
            'assignee_id' => $this->user->id,
            'title' => 'Apple'
        ])->create();

        foreach (TicketField::defaultTypes() as $id => $field) {
            $this->ticket->ticketFields()->create([
                'workspace_id' => $this->user->workspace_id,
                'type' => $field,
                'name' => Str::title($field),
                'order' => $id,
            ]);
        }

        foreach (TicketField::customTypes() as $id => $field) {
            $this->ticket->ticketFields()->create([
                'workspace_id' => $this->user->workspace_id,
                'type' => $field,
                'name' => Str::title($field),
                'order' => $id,
            ]);
        }

        $layer = Layer::factory()->create([
            'project_id' => $project->id,
            'workspace_id' => $this->user->workspace_id,
            'title' => 'NewLayer',
        ]);

       /* $this->ticket = Ticket::factory([
            'workspace_id' => $this->user->workspace_id,
            'ticket_type_id' => $ticketType->id,
            'project_id' => $project->id,
            'author_id' => $this->user->id,
            'assignee_id' => $this->user->id,
            'status' => Ticket::STATUS_OPEN,
        ])->create();*/
    }
}

<?php

namespace Database\Seeders;

use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Workspace::all()->each(function (Workspace $workspace) {

            /**
             * @var TicketType $task
             */
            $task = TicketType::factory()->create([
                'name' => 'Task',
                'title' => 'Title',
                'author_id' => $workspace->owner->id,
                'workspace_id' => $workspace->id,
            ]);

            foreach (TicketField::defaultTypes() as $id => $field) {
                $task->ticketFields()->create([
                    'workspace_id' => $workspace->id,
                    'type' => $field,
                    'name' => Str::title($field),
                    'order' => $id,
                ]);
            }
            /**
             * @var TicketType $inspection
             */
            $inspection = TicketType::factory()->create([
                'name' => 'Inspection',
                'title' => 'Title',
                'author_id' => $workspace->owner->id,
                'workspace_id' => $workspace->id,
            ]);

            foreach (TicketField::customTypes() as $id => $field) {
                $inspection->ticketFields()->create([
                    'workspace_id' => $workspace->id,
                    'type' => $field,
                    'name' => Str::title($field),
                    'order' => $id,
                ]);
            }

            TicketType::factory()->create([
                'name' => 'Bug',
                'title' => 'Title',
                'author_id' => $workspace->owner->id,
                'workspace_id' => $workspace->id,
            ]);

            TicketType::factory()->count(env('SEEDER_TICKET_TYPE', 5))->create([
                'author_id' => $workspace->owner->id,
                'workspace_id' => $workspace->id,
            ]);
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Ticket::all()->each(function (Ticket $ticket) {
            Comment::factory()->count(env('SEEDER_TICKET_COMMENT', 2))->create([
                'workspace_id' => $ticket->workspace_id,
                'ticket_id' => $ticket->id,
            ]);
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Workspace::all()->each(function (Workspace $workspace) {
            Client::factory()->count(env('SEEDER_CLIENT', 4))->create([
                'workspace_id' => $workspace->id,
            ]);
        });
    }
}

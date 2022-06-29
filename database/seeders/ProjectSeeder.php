<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Workspace::all()->each(function (Workspace $workspace) {
            $client = Client::factory(['workspace_id' => $workspace->id])->create();

            Project::factory()->count(env('SEEDER_PROJECT', 2))->create([
                'owner_id' => $workspace->owner_id,
                'workspace_id' => $workspace->id,
                'client_id' => $client->id,
            ])->each(function (Project $project) use ($workspace) {
                User::factory()->count(env('SEEDER_PROJECT_USER', 2))
                    ->create()
                    ->each(function (User $user) use ($workspace) {
                        $workspace->members()->attach($user->id);
                    });
                $project->members()->sync($workspace->members()->inRandomOrder()->limit(5)->get()->pluck('id'));
            });
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermissionSeeder::class);

        User::factory()->create([
            'full_name' => 'John Doe',
            'email' => 'johndoe@example.com',
        ]);

        $this->call([
            UserSeeder::class,
            //            WorkspaceSeeder::class,
            ProjectSeeder::class,
            GroupSeeder::class,
            ClientSeeder::class,
            TicketTypeSeeder::class,
            TicketSeeder::class,
            CustomFieldSeeder::class,
            InvitationSeeder::class,
            LayerSeeder::class,
        ]);
    }
}

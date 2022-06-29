<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = env('SEEDER_USER', 10);
        for ($i = 1; $i <= $count; $i++) {
            User::factory()->create(['email' => "johndoe$i@example.com"]);
        }
    }
}

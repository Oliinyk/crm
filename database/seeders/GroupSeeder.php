<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Workspace::all()->each(function (Workspace $workspace) {
            $count = env('SEEDER_GROUP', 5);
            /**
             * Create User role.
             * @var Role $userRole
             */
            $userRole = Role::create([
                'name' => 'User',
                'workspace_id' => $workspace->id,
            ]);

            Group::factory()
                ->count($count)
                ->create(['workspace_id' => $workspace->id])
                ->each(function (Group $group) use ($workspace, $userRole) {
                    $count = env('SEEDER_GROUP_MEMBER', 4);
                    /**
                     * Check if workspace has enough members.
                     * Add missing members.
                     */
                    if ($workspace->members()->count() < $count) {
                        $users = User::factory()->count($count)->create(['workspace_id' => $workspace->id]);
                        $workspace->members()->attach($users);
                        app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);
                        $users->each->assignRole($userRole);
                    }
                    $ids = $workspace->members()
                        ->inRandomOrder()
                        ->limit($count)
                        ->get()
                        ->pluck('id');
                    $group->members()->attach($ids);
                });
        });
    }
}

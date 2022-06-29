<?php

namespace Database\Seeders;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;

class InvitationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Workspace::all()->each(function (Workspace $workspace) {
            Invitation::factory()
                ->count(env('SEEDER_WORKSPACE_INVITATION', 2))
                ->create([
                    'workspace_id' => $workspace->id,
                    'author_id' => $workspace->owner_id,
                ]);
        });

        User::all()->each(function (User $user) {
            $crtcnt = env('SEEDER_USER_INVITATION', 2);

            $count = Workspace::whereDoesntHave(
                'members',
                fn (Builder $query) => $query->where('user_id', $user->id)->where('member_type', Workspace::class)
            )->count();
            /**
             * TODO: finish this
             */
            if ($count < $crtcnt) {
                //
            }
            Workspace::whereDoesntHave('members', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->limit(env('SEEDER_USER_INVITATION', 2))
                ->get()
                ->each(function (Workspace $workspace) use ($user) {
                    Invitation::factory()
                        ->create([
                            'workspace_id' => $workspace->id,
                            'email' => $user->email,
                            'author_id' => $workspace->owner_id,
                        ]);
                });
        });
    }
}

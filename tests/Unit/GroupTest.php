<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function group_belongs_to_workspace()
    {
        $group = Group::factory()->create();

        $this->assertInstanceOf(Workspace::class, $group->workspace);
    }

    /**
     * @test
     */
    public function group_has_many_members()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $group->members()->attach($user);

        $this->assertInstanceOf(User::class, $group->members->first());
        $this->assertSame($user->id, $group->members->first()->id);
    }
}

<?php

namespace Tests\Feature\Group;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexGroupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Group
     */
    public $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->group = Group::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->group->members()->attach($this->user->id);
    }

    /**
     * @test
     */
    public function user_can_see_group_users()
    {
        $this->index()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Groups/Show')
                ->has('groups', 1)
                ->has('groups.0', fn (Assert $page) => $page
                    ->where('id', $this->group->id)
                    ->where('name', $this->group->name)
                    ->etc()
                )
                ->has('users.data.0', fn (Assert $page) => $page
                    ->where('id', $this->user->id)
                    ->where('full_name', $this->user->full_name)
                    ->etc()
                )
            );
    }

    /**
     * @return TestResponse
     */
    public function index()
    {
        return $this->actingAs($this->user)
            ->get(route('group.show', [
                'workspace' => $this->user->workspace_id,
                'group' => $this->group
            ]));
    }
}

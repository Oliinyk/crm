<?php

namespace Tests\Feature\Group;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SearchGroupMembersTest extends TestCase
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

        User::factory()
            ->count(10)
            ->create([
                'workspace_id' => $this->user->workspace_id,
            ]);
    }

    /**
     * @test
     */
    public function user_can_search_users()
    {
        $this->index()
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'name' => $this->user->full_name,
            ]);
    }

    /**
     * @test
     */
    public function search_query_can_be_empty()
    {
        $this->index(['search' => ''])
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * @test
     */
    public function user_can_search_users_except_selected_members()
    {
        $this->index(['selectedOptions' => [$this->user->toJson()]])
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    /**
     * @return TestResponse
     */
    public function index($overwrites = [])
    {
        $route = route('group.index', array_merge([
            'workspace' => $this->user->workspace_id,
            'search' => $this->user->full_name,
            'group' => $this->group,
        ], $overwrites));

        return $this->actingAs($this->user)
            ->get($route);
    }
}

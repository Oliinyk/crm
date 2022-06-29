<?php

namespace Tests\Feature\User;

use App\Models\Group;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexUserTest extends TestCase
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

    /**
     * @var Invitation|Collection|Model
     */
    public $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->group = Group::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->invitation = Invitation::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);
    }

    /**
     * @test
     */
    public function user_can_see_groups()
    {
        $this->index()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->has('groups', 1)
                ->has('groups.0', fn (Assert $page) => $page
                    ->where('id', $this->group->id)
                    ->where('name', $this->group->name)
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_can_see_invitations()
    {
        Invitation::factory()->count(2)->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->index()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->has('invitations.data', 3)
                ->has('invitations.data.0', fn (Assert $page) => $page
                    ->where('email', $this->invitation->email)
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
            ->get(route('user.index', $this->user->workspace_id));
    }
}

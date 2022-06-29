<?php

namespace Tests\Feature\Ticket\Index;

use App\Enums\PermissionsEnum;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TableTicketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Ticket
     */
    public $ticket;

    /**
     * @var Project
     */
    public $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->project = Project::factory()->create([
            'workspace_id' => $this->user->workspace_id,
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_PROJECTS->value);
        $this->user->givePermissionTo(PermissionsEnum::SEE_ALL_TICKETS->value);
    }

    /**
     * @test
     */
    public function it_must_remember_tickets_in_cache()
    {
        Cache::spy();

        $this->index()
            ->assertStatus(200);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("user.{$this->user->id}.tickets.table");

        Cache::shouldHaveReceived('rememberForever')
            ->once();
    }

    /**
     * @return TestResponse
     */
    public function index($overwrites = [])
    {
        $route = route('ticket.table', array_merge([
            'project' => $this->project,
            'workspace' => $this->user->workspace_id
        ], $overwrites));

        return $this->actingAs($this->user)->post($route);
    }
}

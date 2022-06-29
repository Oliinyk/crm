<?php

namespace Tests\Unit;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvivationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function invitation_belongs_to_the_workspace()
    {
        $workspace = Workspace::factory()->create();

        $invitation = Invitation::factory()->create(['workspace_id' => $workspace->id]);

        $this->assertInstanceOf(Workspace::class, $invitation->workspace);
        $this->assertSame($workspace->id, $invitation->workspace->id);
    }

    /**
     * @test
     */
    public function invitation_belongs_to_the_author()
    {
        $user = User::factory()->create();

        $invitation = Invitation::factory()->create(['author_id' => $user->id]);

        $this->assertInstanceOf(User::class, $invitation->author);
        $this->assertSame($user->id, $invitation->author->id);
    }
}

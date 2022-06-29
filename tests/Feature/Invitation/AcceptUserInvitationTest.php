<?php

namespace Tests\Feature\Invitation;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AcceptUserInvitationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Workspace|Collection|Model
     */
    public $workspace;

    /**
     * @var Invitation|Collection|Model
     */
    public $invitation;

    /**
     * @var User|Collection|Model
     */
    public $user;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->user = User::factory()->create();

        $this->workspace = Workspace::factory()->create();

        $this->invitation = Invitation::factory()->create([
            'workspace_id' => $this->workspace->id,
            'email' => $this->user->email,
        ]);
    }

    /**
     * @test
     */
    public function after_successful_invitation_user_must_be_a_member_of_the_workspace()
    {
        $this->withoutExceptionHandling();
        $this->accept()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('members', [
            'user_id' => $this->user->id,
            'member_type' => Workspace::class,
            'member_id' => $this->workspace->id,
        ]);
    }

    /**
     * @test
     */
    public function it_must_delete_invitation_after_successful_acceptation()
    {
        $this->assertDatabaseHas('invitations', ['id' => $this->invitation->id]);

        $this->accept()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('invitations', ['id' => $this->invitation->id]);
    }

    public function accept()
    {
        $route = route('invitation.accept.user', [
            'workspace' => $this->user->workspace_id,
            'token' => $this->invitation->token
        ]);

        return $this->actingAs($this->user)->post($route);
    }
}

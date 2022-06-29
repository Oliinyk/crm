<?php

namespace Tests\Feature\Invitation;

use App\Enums\PermissionsEnum;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DeclineUserInvitationTest extends TestCase
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

    /**
     * @var User|Collection|Model
     */
    public $invitationAuthor;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->user = User::factory()->create();

        $this->invitationAuthor = User::factory()->create();

        $this->workspace = Workspace::find($this->invitationAuthor->workspace_id);

        $this->invitation = Invitation::factory()->create([
            'workspace_id' => $this->workspace->id,
            'email' => $this->user->email,
            'author_id' => $this->invitationAuthor->id
        ]);

        $this->invitationAuthor->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->invitationAuthor->workspace_id);
        $this->invitationAuthor->givePermissionTo(PermissionsEnum::MANAGE_INVITATIONS->value);
    }

    /**
     * @test
     */
    public function member_of_the_workspace_can_delete_any_of_the_workspace_invitation()
    {
        $this->assertDatabaseCount('invitations', 1);

        $this->decline()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('invitations', 0);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_delete_an_invitation()
    {
        $this->invitationAuthor->revokePermissionTo(PermissionsEnum::MANAGE_INVITATIONS->value);

        $this->decline()->assertStatus(403);
    }

    public function decline()
    {
        $route = route('invitation.decline.user', [
            'workspace' => $this->invitationAuthor->workspace_id,
            'token' => $this->invitation->token
        ]);

        return $this->actingAs($this->invitationAuthor)->delete($route);
    }
}

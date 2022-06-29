<?php

namespace Tests\Feature\Invitation;

use App\Enums\PermissionsEnum;
use App\Models\Invitation;
use App\Models\Layer;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\InvitationNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CreateInvitationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Workspace|Collection|Model
     */
    public $workspace;

    /**
     * @var Project|Collection|Model
     */
    public $project;

    /**
     * @var Layer|Collection|Model
     */
    public $parentLayer;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        Storage::fake('public');

        $this->user = User::factory()->create();

        $this->workspace = $this->user->workspaces()->first();

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::MANAGE_INVITATIONS->value);
    }

    /**
     * @test
     */
    public function user_can_invite_guest_to_the_workspace()
    {
        $this->invite()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('invitations', [
            'email' => 'test@example.com',
            'author_id' => $this->user->id,
            'workspace_id' => $this->workspace->id,
        ]);
    }

    /**
     * @test
     */
    public function user_can_invite_user_to_the_workspace()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->invite()->assertStatus(302)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('invitations', [
            'email' => 'test@example.com',
            'author_id' => $this->user->id,
            'workspace_id' => $this->workspace->id,
        ]);
    }

    /**
     * @test
     */
    public function user_cant_invite_existing_workspace_member()
    {
        $member = User::factory()->create(['email' => 'test@example.com']);

        $this->workspace->members()->attach($member->id);

        $this->invite()->assertSessionHasErrors(['email' => 'User already exists in this workspace.']);

        $this->assertDatabaseCount('invitations', 0);
    }

    /**
     * @test
     */
    public function it_must_generate_random_token()
    {
        $this->invite()->assertStatus(302)->assertSessionHasNoErrors();

        $this->assertNotNull(Invitation::first()->token);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_an_invitation()
    {
        $this->user->revokePermissionTo(PermissionsEnum::MANAGE_INVITATIONS->value);

        $this->invite()->assertStatus(403);
    }

    /**
     * @test
     */
    public function the_email_field_is_required()
    {
        $this->invite(['email' => ''])
            ->assertSessionHasErrors(['email' => 'The email field is required.']);
    }

    /**
     * @test
     */
    public function the_email_field_must_be_valid_email()
    {
        $this->invite(['email' => 'test'])
            ->assertSessionHasErrors(['email' => 'The email must be a valid email address.']);
    }

    /**
     * @test
     */
    public function user_can_resend_invitation()
    {
        $invitation = Invitation::factory()->create([
            'email' => 'current_workspace@example.com',
            'workspace_id' => $this->workspace->id,
        ]);

        $this->assertDatabaseHas('invitations', ['token' => $invitation->token]);

        $this->invite(['email' => 'current_workspace@example.com'])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseMissing('invitations', ['token' => $invitation->token]);

        $this->assertDatabaseHas('invitations', ['token' => $invitation->fresh()->token]);
    }

    /**
     * @test
     */
    public function it_must_fire_an_email_notification()
    {
        $this->invite(['email' => 'test@example.com']);

        // Assert a notification was sent to the given users...
        Notification::assertSentTo(
            new AnonymousNotifiable,
            InvitationNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'test@example.com';
            }
        );
    }

    /**
     * @return TestResponse
     */
    public function invite($overwtites = [])
    {
        $route = route('invitation.store', ['workspace' => $this->workspace->id]);

        return $this->actingAs($this->user)
            ->post($route, array_merge([
                'email' => 'test@example.com',
            ], $overwtites));
    }
}

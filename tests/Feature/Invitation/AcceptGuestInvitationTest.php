<?php

namespace Tests\Feature\Invitation;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Str;
use Tests\TestCase;

class AcceptGuestInvitationTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        User::unsetEventDispatcher();

        Notification::fake();

        $this->workspace = Workspace::factory()->create();

        $this->invitation = Invitation::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);
    }

    /**
     * @test
     */
    public function guest_can_see_accept_invitation_page()
    {
        $route = route('invitation.show', $this->invitation->token);

        $this->get($route)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Invitation')
                ->has('invitation.data', fn (Assert $page) => $page
                    ->where('email', $this->invitation->email)
                    ->where('token', $this->invitation->token)
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function guest_can_accept_invitation()
    {
        $this->accept()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => $this->invitation->email,
            'workspace_id' => $this->invitation->workspace_id,
            'full_name' => 'test',
        ]);
    }

    /**
     * @test
     */
    public function after_successful_invitation_user_must_be_a_member_of_the_workspace()
    {
        $this->accept()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('members', [
            'user_id' => $user = User::firstWhere('email', $this->invitation->email)->id,
            'member_type' => Workspace::class,
            'member_id' => $this->workspace->id,
        ]);
    }

    /**
     * @test
     */
    public function after_successful_invitation_user_must_log_in()
    {
        $this->accept()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs(User::firstWhere('email', $this->invitation->email));
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

    /**
     * @test
     */
    public function full_name_field_is_required()
    {
        $this->accept(['full_name' => ''])
            ->assertSessionHasErrors(['full_name' => 'The full name field is required.']);
    }

    /**
     * @test
     */
    public function full_name_field_may_not_be_greater_than_50_characters()
    {
        $this->accept(['full_name' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['full_name' => 'The full name must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function password_field_is_required()
    {
        $this->accept(['password' => ''])
            ->assertSessionHasErrors(['password' => 'The password field is required.']);
    }

    /**
     * @test
     */
    public function password_field_may_not_be_greater_than_50_characters()
    {
        $this->accept(['password' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['password' => 'The password must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function password_field_may_not_be_at_least_8_characters()
    {
        $this->accept(['password' => 'a'])
            ->assertSessionHasErrors(['password' => 'The password must be at least 8 characters.']);
    }

    public function accept($overwrites = [])
    {
        $route = route('invitation.accept.guest', $this->invitation->token);

        return $this->post($route, array_merge([
            'full_name' => 'test',
            'password' => '12345678',
        ], $overwrites));
    }
}

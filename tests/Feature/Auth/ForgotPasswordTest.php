<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->user = User::factory()->create();
    }

    /**
     * @test
     */
    public function guest_can_see_forgot_password_page()
    {
        $this->get(route('password.request'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/PasswordRequest'));
    }

    /**
     * @test
     */
    public function guest_can_send_forgot_password_form()
    {
        Carbon::setTestNow('2020-01-01');

        $this->request()
            ->assertSessionHas('success', 'We have e-mailed your password reset link!');

        $this->assertDatabaseHas('password_resets', [
            'email' => $this->user->email,
            'created_at' => Carbon::now(),
        ]);

        Notification::assertSentTo($this->user, ResetPassword::class);
    }

    /**
     * @test
     */
    public function guest_must_see_throttle_message()
    {
        $this->request()
            ->assertSessionHas('success', 'We have e-mailed your password reset link!');

        $this->request()
            ->assertSessionHasErrors(['email' => 'Please wait before trying again.']);
    }

    /**
     * @test
     */
    public function email_field_is_required()
    {
        $this->request(['email' => ''])
            ->assertSessionHasErrors(['email' => 'The email field is required.']);
    }

    /**
     * @test
     */
    public function email_field_must_be_valid_email_address()
    {
        $this->request(['email' => 'test'])
            ->assertSessionHasErrors(['email' => 'The email must be a valid email address.']);
    }

    /**
     * @test
     */
    public function user_must_exist_in_the_database()
    {
        $this->request(['email' => 'unexisting@example.com'])
            ->assertSessionHasErrors(['email' => 'We can\'t find a user with that e-mail address.']);
    }

    /**
     * @param array $overwrite
     * @return TestResponse
     */
    public function request($overwrite = [])
    {
        return $this->post(route('password.email'), array_merge([
            'email' => $this->user->email,
        ], $overwrite));
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use DB;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    public $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->token = Password::broker()->createToken($this->user);

        DB::table('password_resets')->insert([
            'email' => $this->user->email,
            'token' => $this->token,
            'created_at' => now(),
        ]);

        Event::fake();
    }

    /**
     * @test
     */
    public function guest_can_see_reset_password_page()
    {
        $route = route('password.reset', '123');

        $this->get($route)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/PasswordReset')
                ->where('token', '123')
            );
    }

    /**
     * @test
     */
    public function guest_can_send_reset_password_form()
    {
        $this->assertFalse(Hash::check('12345678', $this->user->password));

        $this->request()
            ->assertSessionHas('success', 'Your password has been reset!');

        $this->user->refresh();

        $this->assertTrue(Hash::check('12345678', $this->user->password));

        Event::assertDispatched(PasswordReset::class);
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
     * @test
     */
    public function token_field_is_required()
    {
        $this->request(['token' => ''])
            ->assertSessionHasErrors(['token' => 'The token field is required.']);
    }

    /**
     * @test
     */
    public function token_field_must_be_valid()
    {
        $this->request(['token' => '123'])
            ->assertSessionHasErrors(['email' => 'This password reset token is invalid.']);
    }

    /**
     * @test
     */
    public function password_field_is_required()
    {
        $this->request(['password' => ''])
            ->assertSessionHasErrors(['password' => 'The password field is required.']);
    }

    /**
     * @test
     */
    public function password_field_must_be_at_least_8_characters()
    {
        $this->request(['password' => '132'])
            ->assertSessionHasErrors(['password' => 'The password must be at least 8 characters.']);
    }

    /**
     * @test
     */
    public function password_field_must_be_confirmed()
    {
        $this->request(['password' => '12345677'])
            ->assertSessionHasErrors(['password' => 'The password confirmation does not match.']);
    }

    /**
     * @param array $overwrite
     * @return TestResponse
     */
    public function request($overwrite = [])
    {
        return $this->post(route('password.update'), array_merge([
            'email' => $this->user->email,
            'token' => $this->token,
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ], $overwrite));
    }
}

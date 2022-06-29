<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    public $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * @test
     */
    public function user_can_login()
    {
        $this->login()->assertStatus(302);

        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * @test
     */
    public function user_can_see_login_page()
    {
        $this->get(route('login'))
            ->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
    }

    /**
     * @test
     */
    public function user_can_log_out()
    {
        $route = route('logout', ['workspace' => $this->user->workspace_id]);

        $this->actingAs($this->user)
            ->delete($route)
            ->assertStatus(302);

        $this->assertGuest();
    }

    /**
     * @test
     */
    public function email_field_is_required()
    {
        $this->login(['email' => ''])
            ->assertSessionHasErrors(['email' => 'The email field is required.']);
    }

    /**
     * @test
     */
    public function the_password_field_is_required()
    {
        $this->login(['password' => null])
            ->assertSessionHasErrors(['password' => 'The password field is required.']);
    }

    /**
     * @test
     */
    public function too_many_attempts_test()
    {
        $this->artisan('cache:clear');

        foreach (range(1, 10) as $item) {
            $this->login(['password' => '123123123']);
        }

        $this->login(['password' => '123123123'])
            ->assertSessionHasErrors(['email' => 'Too many login attempts. Please try again in 60 seconds.']);

        $this->artisan('cache:clear');
    }

    /**
     * @return TestResponse
     */
    public function login($overwrite = [])
    {
        return $this->post(route('login.store'), array_merge([
            'email' => $this->user->email,
            'password' => 'secret',
        ], $overwrite));
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function user_can_register()
    {
        $this->register()->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'full_name' => 'test',
            'email' => 'test@test.com',
            'locale' => $this->app->getLocale(),
        ]);
    }

    /**
     * @test
     */
    public function user_can_see_register_page()
    {
        $this->get(route('register'))
            ->assertInertia(fn (Assert $page) => $page->component('Auth/Register'));
    }

    /**
     * @test
     */
    public function full_name_field_is_required()
    {
        $this->register(['full_name' => ''])
            ->assertSessionHasErrors(['full_name' => 'The full name field is required.']);
    }

    /**
     * @test
     */
    public function the_name_may_not_be_greater_than_50_characters()
    {
        $this->register(['full_name' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['full_name' => 'The full name must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function email_field_is_required()
    {
        $this->register(['email' => ''])
            ->assertSessionHasErrors(['email' => 'The email field is required.']);
    }

    /**
     * @test
     */
    public function the_email_may_not_be_greater_than_50_characters()
    {
        $this->register(['email' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['email' => 'The email must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_email_field_must_be_unique()
    {
        $user = User::factory()->create();
        $this->register(['email' => $user->email])
            ->assertSessionHasErrors(['email' => 'The email has already been taken.']);
    }

    /**
     * @test
     */
    public function the_password_field_is_required()
    {
        $this->register(['password' => null])
            ->assertSessionHasErrors(['password' => 'The password field is required.']);
    }

    /**
     * @test
     */
    public function The_password_must_be_at_least_8_characters()
    {
        $this->register(['password' => '123'])
            ->assertSessionHasErrors(['password' => 'The password must be at least 8 characters.']);
    }

    /**
     * @test
     */
    public function the_password_field_must_match()
    {
        $this->register(['password' => '123123123'])
            ->assertSessionHasErrors(['password' => 'The password confirmation does not match.']);
    }

    /**
     * @test
     */
    public function too_many_attempts_test()
    {
        $this->artisan('cache:clear');
        Carbon::setTestNow('2020-01-01');

        foreach (range(1, 11) as $item) {
            $this->register(['password' => '123123123']);
        }

        $this->register(['password' => '123123123'])
            ->assertSessionHasErrors(['email' => 'Too many login attempts. Please try again in 60 seconds.']);

        $this->artisan('cache:clear');
    }

    /**
     * @return TestResponse
     */
    public function register($overwrite = [])
    {
        return $this->post(route('register.store'), array_merge([
            'full_name' => 'test',
            'email' => 'test@test.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ], $overwrite));
    }
}

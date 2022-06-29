<?php

namespace Tests\Feature\Client;

use App\Enums\PermissionsEnum;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CreateClientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);

        $this->user->givePermissionTo(PermissionsEnum::ADD_CLIENTS->value);
    }

    /**
     * @test
     */
    public function user_can_create_client()
    {
        $this->create()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'name' => 'test',
            'workspace_id' => $this->user->workspace_id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_a_client()
    {
        $this->user->revokePermissionTo(PermissionsEnum::ADD_CLIENTS->value);

        $this->create()->assertStatus(403);
    }

    /**
     * @test
     */
    public function the_name_field_is_required()
    {
        $this->create(['name' => ''])
            ->assertSessionHasErrors(['name' => 'The name field is required.']);
    }

    /**
     * @test
     */
    public function the_name_may_not_be_greater_than_50_characters()
    {
        $this->create(['name' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['name' => 'The name must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_status_may_not_be_greater_than_50_characters()
    {
        $this->create(['status' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['status' => 'The status must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_email_field_must_be_valid_email()
    {
        $this->create(['email' => 'test'])
            ->assertSessionHasErrors(['email' => 'The email must be a valid email address.']);
    }

    /**
     * @test
     */
    public function the_email_field_must_be_unique_in_current_workspace()
    {
        Client::factory()->create([
            'workspace_id' => $this->user->workspace_id,
            'email' => 'test@test.com'
        ]);

        $this->create(['email' => 'test@test.com'])
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => 'The email has already been taken.']);
    }

    /**
     * @test
     */
    public function the_email_may_not_be_greater_than_50_characters()
    {
        $this->create(['email' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['email' => 'The email must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_phone_may_not_be_greater_than_50_characters()
    {
        $this->create(['phone' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['phone' => 'The phone must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_city_may_not_be_greater_than_50_characters()
    {
        $this->create(['city' => Str::repeat('a', 51)])
            ->assertStatus(302)
            ->assertSessionHasErrors(['city' => 'The city must not be greater than 50 characters.']);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        $route = route('client.store', ['workspace' => $this->user->workspace_id]);

        return $this->actingAs($this->user)
            ->post($route, array_merge([
                'name' => 'test',
                'status' => 'test',
                'email' => 'test@test.com',
                'phone' => 'test',
                'city' => 'test',
            ], $overwtites));
    }
}

<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
    }

    /**
     * @test
     */
    public function user_can_be_updated()
    {
        $this->update()->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'full_name' => 'test',
            'email' => 'test@test.com',
        ]);
    }

    /**
     * @test
     */
    public function image_must_exist_in_a_storage()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $file = Storage::disk('public')->putFileAs('temp/', $file, $file->getClientOriginalName());

        $this->update(['image' => ['url' => $file, 'id' => 123]])
            ->assertStatus(302);

        $image = $this->user->getFirstMedia();

        Storage::disk('public')->assertExists($image->id.'/'.$image->file_name);
    }

    /**
     * @test
     */
    public function user_must_delete_old_image_after_update()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $file = Storage::disk('public')->putFileAs('temp/', $file, $file->getClientOriginalName());

        $this->update(['image' => ['url' => $file, 'id' => 123]]);

        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file1 = Storage::disk('public')->putFileAs('temp/', $file1, $file1->getClientOriginalName());

        $this->update(['image' => ['url' => $file1, 'id' => 234]]);

        $this->assertSame(1, $this->user->media()->count());
    }

    /**
     * @test
     */
    public function the_image_field_can_be_empty()
    {
        $this->update(['image' => ''])->assertStatus(302)->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function full_name_field_is_required()
    {
        $this->update(['full_name' => ''])
            ->assertSessionHasErrors(['full_name' => 'The full name field is required.']);
    }

    /**
     * @test
     */
    public function the_full_name_may_not_be_greater_than_50_characters()
    {
        $this->update(['full_name' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['full_name' => 'The full name must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function email_field_is_required()
    {
        $this->update(['email' => ''])
            ->assertSessionHasErrors(['email' => 'The email field is required.']);
    }

    /**
     * @test
     */
    public function the_email_may_not_be_greater_than_50_characters()
    {
        $this->update(['email' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['email' => 'The email must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_email_must_be_a_valid_email_address()
    {
        $this->update(['email' => 'test'])
            ->assertSessionHasErrors(['email' => 'The email must be a valid email address.']);
    }

    /**
     * @test
     */
    public function email_field_must_be_unique()
    {
        $user1 = User::factory()->create();

        $this->update(['email' => $user1->email])
            ->assertSessionHasErrors(['email' => 'The email has already been taken.']);
    }

    /**
     * @test
     */
    public function the_password_must_be_at_least_8_characters()
    {
        $this->update(['password' => '1'])
            ->assertSessionHasErrors(['password' => 'The password must be at least 8 characters.']);
    }

    /**
     * @test
     */
    public function the_password_may_not_be_greater_than_50_characters()
    {
        $this->update(['password' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['password' => 'The password must not be greater than 50 characters.']);
    }

    /**
     * @param array $overwrites
     * @return TestResponse
     */
    public function update($overwrites = [])
    {
        return $this->actingAs($this->user)->put(route('user.update', [
            'workspace' => $this->user->workspace_id,
            'user' => $this->user
        ]), array_merge([
            'full_name' => 'test',
            'email' => 'test@test.com',
        ], $overwrites));
    }
}

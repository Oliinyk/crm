<?php

namespace Tests\Feature\Workspace;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UpdateWorkspaceTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();

        $this->workspace = $this->user->workspaces()->first();
    }

    /**
     * @test
     */
    public function user_can_update_workspace()
    {
        $this->assertDatabaseMissing('workspaces', [
            'id' => $this->workspace->id,
            'name' => 'test',
        ]);

        $this->update()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('workspaces', [
            'id' => $this->workspace->id,
            'name' => 'test',
        ]);
    }

    /**
     * @test
     */
    public function image_must_exist_in_a_storage()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $file = Storage::disk('public')->putFileAs('temp/', $file, $file->getClientOriginalName());

        $this->update(['photo' => ['url' => $file, 'id' => 123]])
            ->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        $image = $this->workspace->fresh()->getFirstMedia();

        Storage::disk('public')->assertExists($image->id.'/'.$image->file_name);
    }

    /**
     * @test
     */
    public function it_can_update_workspace_without_url()
    {
        $this->update(['photo' => ['url' => null, 'id' => null]])
            ->assertStatus(302)
            ->assertSessionDoesntHaveErrors();
    }

    /**
     * @test
     */
    public function workspace_must_delete_old_image_after_update()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $file = Storage::disk('public')->putFileAs('temp/', $file, $file->getClientOriginalName());

        $this->update(['photo' => ['url' => $file, 'id' => 123]]);

        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file1 = Storage::disk('public')->putFileAs('temp/', $file1, $file1->getClientOriginalName());

        $this->update(['photo' => ['url' => $file1, 'id' => 234]]);

        $this->assertSame(1, $this->workspace->media()->count());
    }

    /**
     * @test
     */
    public function the_name_field_is_required()
    {
        $this->update(['name' => ''])
            ->assertSessionHasErrors(['name' => 'The name field is required.']);
    }

    /**
     * @test
     */
    public function the_name_field_may_not_be_greater_than_50_characters()
    {
        $this->update(['name' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['name' => 'The name must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_plan_field_is_required()
    {
        $this->update(['plan' => ''])
            ->assertSessionHasErrors(['plan' => 'The plan field is required.']);
    }

    /**
     * @test
     */
    public function the_plan_field_may_not_be_greater_than_50_characters()
    {
        $this->update(['plan' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['plan' => 'The plan must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_photo_field_can_be_empty()
    {
        $this->update(['photo' => ''])->assertStatus(302)->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function the_photo_field_must_be_an_image()
    {
        $file = UploadedFile::fake()->create('avatar.txt');
        $file = Storage::disk('public')->putFileAs('temp/', $file, $file->getClientOriginalName());

        $this->update(['photo' => ['url' => $file, 'id' => 123]])
            ->assertSessionHasErrors(['photo' => 'The file must be a file of type:jpg, png']);
    }

    /**
     * @return TestResponse
     */
    public function update($overwrites = [])
    {
        return $this->actingAs($this->user)
            ->put(route('workspace.update', $this->workspace), array_merge([
                'name' => 'test',
                'plan' => 'test',
            ], $overwrites));
    }
}

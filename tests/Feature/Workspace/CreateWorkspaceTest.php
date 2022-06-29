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

class CreateWorkspaceTest extends TestCase
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
    public function user_can_create_workspace()
    {
        $this->store()->assertStatus(302);

        /**
         * Checking if the database has workspaces.
         * Checking that the user was attached as the owner of the workspace.
         */
        $this->assertDatabaseHas('workspaces', [
            'name' => 'test',
            'owner_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_be_attached_as_a_member()
    {
        $this->assertDatabaseCount('members', 1);

        $this->store()->assertStatus(302);

        $this->assertDatabaseCount('members', 2);
    }

    /**
     * @test
     */
    public function user_must_have_admin_permission_in_newly_created_workspace()
    {
        $this->store()->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('roles', [
            'name' => 'Administrator',
            'workspace_id' => Workspace::whereName('test')->firstOrFail()->id,
        ]);

        $this->assertDatabaseHas('model_has_roles', [
            'model_type' => User::class,
            'workspace_id' => Workspace::whereName('test')->firstOrFail()->id,
            'model_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     */
    public function image_must_exist_in_a_storage()
    {
        $this->store()
            ->assertStatus(302)
            ->assertSessionDoesntHaveErrors();

        $image = $this->user->ownWorkspaces->last()->getFirstMedia();

        Storage::disk('public')->assertExists($image->id.'/'.$image->file_name);
    }

    /**
     * @test
     */
    public function the_name_field_is_required()
    {
        $this->store(['name' => ''])
            ->assertSessionHasErrors(['name' => 'The name field is required.']);
    }

    /**
     * @test
     */
    public function the_name_field_may_not_be_greater_than_50_characters()
    {
        $this->store(['name' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['name' => 'The name must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_plan_field_is_required()
    {
        $this->store(['plan' => ''])
            ->assertSessionHasErrors(['plan' => 'The plan field is required.']);
    }

    /**
     * @test
     */
    public function the_plan_field_may_not_be_greater_than_50_characters()
    {
        $this->store(['plan' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['plan' => 'The plan must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_photo_field_must_be_an_image()
    {
        $file = UploadedFile::fake()->create('avatar.txt');
        $file = Storage::disk('public')->putFileAs('temp', $file, $file->getClientOriginalName());

        $this->store(['photo' => ['url' => $file, 'id' => 123]])
            ->assertStatus(302)
            ->assertSessionHasErrors(['photo' => 'The file must be a file of type:jpg, png']);
    }

    /**
     * @test
     */
    public function the_photo_field_can_be_empty()
    {
        $this->store(['photo' => ''])->assertStatus(302)->assertSessionHasNoErrors();
    }

    /**
     * @return TestResponse
     */
    public function store($overwrite = [])
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $file = Storage::disk('public')->putFileAs('temp', $file, $file->getClientOriginalName());

        $route = route('workspace.store', $this->user->workspace_id);

        return $this->actingAs($this->user)
            ->post($route, array_merge([
                'name' => 'test',
                'plan' => 'test',
                'photo' => ['url' => $file, 'id' => 123],
            ], $overwrite));
    }
}

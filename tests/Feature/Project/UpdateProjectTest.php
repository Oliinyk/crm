<?php

namespace Tests\Feature\Project;

use App\Enums\PermissionsEnum;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UpdateProjectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User|Collection|Model
     */
    public $user;

    /**
     * @var Project|Collection|Model
     */
    public $project;

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
        $this->project = Project::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::EDIT_ALL_PROJECTS->value);
    }

    /**
     * @test
     */
    public function user_can_update_project()
    {
        $this->assertDatabaseMissing('projects', [
            'id' => $this->project->id,
            'name' => 'test',
        ]);

        $this->update()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'name' => 'test',
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_update_project()
    {
        $this->user->revokePermissionTo(PermissionsEnum::EDIT_ALL_PROJECTS->value);

        $this->update()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_without_permission_can_update_own_project()
    {
        $this->project->update([
            'owner_id' => $this->user->id,
        ]);

        $this->user->revokePermissionTo(PermissionsEnum::EDIT_ALL_PROJECTS->value);

        $this->update()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'name' => 'test',
        ]);
    }

    /**
     * @test
     */
    public function user_can_update_deleted_project()
    {
        $this->assertDatabaseMissing('projects', [
            'id' => $this->project->id,
            'name' => 'test',
        ]);

        $this->project->delete();

        $this->update()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'name' => 'test',
        ]);
    }

    /**
     * @test
     */
    public function image_must_exist_in_a_storage()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->update(['photo' => $file]);

        $image = $this->project->getFirstMedia();

        Storage::disk('public')->assertExists($image->id.'/'.$image->file_name);
    }

    /**
     * @test
     */
    public function project_must_delete_old_image_after_update()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->update(['photo' => $file]);

        $file1 = UploadedFile::fake()->image('avatar1.jpg');

        $this->update(['photo' => $file1]);

        $this->assertSame(1, $this->project->media()->count());
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

        $this->update(['photo' => $file])
            ->assertSessionHasErrors(['photo' => 'The photo must be an image.']);
    }

    /**
     * @test
     */
    public function the_working_hours_field_must_be_integer()
    {
        $this->update(['working_hours' => 'test'])
            ->assertSessionHasErrors(['working_hours' => 'The working hours must be an integer.']);
    }

    /**
     * @test
     */
    public function the_working_hours_field_is_required()
    {
        $this->update(['working_hours' => ''])
            ->assertSessionHasErrors(['working_hours' => 'The working hours field is required.']);
    }

    /**
     * @return TestResponse
     */
    public function update($overwrites = [])
    {
        return $this->actingAs($this->user)
            ->put(route('project.update', [
                'project' => $this->project,
                'workspace' => $this->workspace
            ]), array_merge([
                'name' => 'test',
                'working_hours' => 8,
            ], $overwrites));
    }
}

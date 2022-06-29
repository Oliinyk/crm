<?php

namespace Tests\Feature\Layer;

use App\Enums\PermissionsEnum;
use App\Models\Layer;
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

class UpdateLayerTest extends TestCase
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

    /**
     * @var Layer|Collection|Model
     */
    public $layer;

    /**
     * @var Layer|Collection|Model
     */
    public $parentLayer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();

        $this->workspace = $this->user->workspaces()->first();

        $this->project = Project::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $this->layer = Layer::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $this->project->id,
        ]);

        $this->parentLayer = Layer::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $this->project->id,
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::EDIT_ALL_LAYERS->value);
    }

    /**
     * @test
     */
    public function user_can_update_layer()
    {
        $this->assertDatabaseMissing('layers', [
            'id' => $this->layer->id,
            'title' => 'test',
        ]);

        $this->update()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('layers', [
            'id' => $this->layer->id,
            'title' => 'test',
            'parent_layer_id' => $this->parentLayer->id,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_update_a_layer()
    {
        $this->user->revokePermissionTo(PermissionsEnum::EDIT_ALL_LAYERS->value);

        $this->update()->assertStatus(403);
    }

    /**
     * @test
     */
    public function user_without_permission_can_update_own_layer()
    {
        $this->layer->update([
            'author_id' => $this->user->id,
        ]);

        $this->user->revokePermissionTo(PermissionsEnum::EDIT_ALL_LAYERS->value);

        $this->update()
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('layers', [
            'id' => $this->layer->id,
            'title' => 'test',
        ]);
    }

    /**
     * @test
     */
    public function image_must_exist_in_a_storage()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->update(['image' => $file]);

        $image = $this->layer->getFirstMedia();

        Storage::disk('public')->assertExists($image->id.'/'.$image->file_name);
    }

    /**
     * @test
     */
    public function layer_must_delete_old_image_after_update()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->update(['image' => $file]);

        $file1 = UploadedFile::fake()->image('avatar1.jpg');

        $this->update(['image' => $file1]);

        $this->assertSame(1, $this->layer->media()->count());
    }

    /**
     * @test
     */
    public function the_title_field_is_required()
    {
        $this->update(['title' => ''])
            ->assertSessionHasErrors(['title' => 'The title field is required.']);
    }

    /**
     * @test
     */
    public function the_title_field_may_not_be_greater_than_50_characters()
    {
        $this->update(['title' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['title' => 'The title must not be greater than 50 characters.']);
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
    public function the_image_field_must_be_an_image()
    {
        $file = UploadedFile::fake()->create('avatar.txt');

        $this->update(['image' => $file])
            ->assertSessionHasErrors(['image' => 'The image must be an image.']);
    }

    /**
     * @test
     */
    public function the_parent_layer_id_must_exist_in_the_database()
    {
        $this->update(['parent_layer_id' => '123'])
            ->assertSessionHasErrors(['parent_layer_id' => 'The selected parent layer id is invalid.']);
    }

    /**
     * @test
     */
    public function the_parent_layer_id_must_exist_in_current_workspace()
    {
        $parentLayer = Layer::factory()->create();

        $this->update(['parent_layer_id' => $parentLayer->id])
            ->assertSessionHasErrors(['parent_layer_id' => 'The selected parent layer id is invalid.']);
    }

    /**
     * @return TestResponse
     */
    public function update($overwrites = [])
    {
        $route = route('layer.update', [
            'project' => $this->project,
            'layer' => $this->layer,
            'workspace' => $this->workspace
        ]);

        return $this->actingAs($this->user)
            ->put($route, array_merge([
                'title' => 'test',
                'parent_layer_id' => $this->parentLayer->id,
            ], $overwrites));
    }
}

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

class CreateLayerTest extends TestCase
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

    /**
     * @var Project|Collection|Model
     */
    public $project;

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

        $this->parentLayer = Layer::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $this->project->id,
        ]);

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::CREATE_LAYERS->value);
    }

    /**
     * @test
     */
    public function user_can_create_a_layer()
    {
        $this->create()->assertStatus(302)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('layers', [
            'title' => 'test',
            'project_id' => $this->project->id,
            'author_id' => $this->user->id,
            'workspace_id' => $this->workspace->id,
            'parent_layer_id' => $this->parentLayer->id,
        ]);
    }

    /**
     * @test
     */
    public function user_can_create_a_layer_image()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->create(['image' => $file])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $image = Layer::whereNotNull('parent_layer_id')->first()->getFirstMedia();

        Storage::disk('public')->assertExists($image->id.'/'.$image->file_name);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_a_layer()
    {
        $this->user->revokePermissionTo(PermissionsEnum::CREATE_LAYERS->value);

        $this->create()->assertStatus(403);
    }

    /**
     * @test
     */
    public function the_title_field_is_required()
    {
        $this->create(['title' => ''])
            ->assertSessionHasErrors(['title' => 'The title field is required.']);
    }

    /**
     * @test
     */
    public function the_title_field_may_not_be_greater_tan_50_characters()
    {
        $this->create(['title' => Str::repeat('a', 51)])
            ->assertSessionHasErrors(['title' => 'The title must not be greater than 50 characters.']);
    }

    /**
     * @test
     */
    public function the_image_field_is_required()
    {
        $this->create(['image' => ''])
            ->assertSessionHasErrors(['image' => 'The image field is required.']);
    }

    /**
     * @test
     */
    public function the_image_field_must_be_image()
    {
        $this->create(['image' => 'test'])
            ->assertSessionHasErrors(['image' => 'The image must be an image.']);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        return $this->actingAs($this->user)
            ->post(route('layer.store', [
                'workspace' => $this->workspace->id,
                'project' => $this->project
            ]), array_merge([
                'title' => 'test',
                'parent_layer_id' => $this->parentLayer->id,
                'image' => $file,
            ], $overwtites));
    }
}

<?php

namespace Tests\Feature\Project;

use App\Enums\PermissionsEnum;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CreateProjectTest extends TestCase
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

        $this->user->removeRole('Administrator');
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->workspace->id);
        $this->user->givePermissionTo(PermissionsEnum::CREATE_PROJECTS->value);
    }

    /**
     * @test
     */
    public function user_can_create_project()
    {
        $this->assertDatabaseCount('projects', 0);

        $this->create()->assertStatus(302)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'name' => 'test',
            'working_hours' => 8,
        ]);
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_create_project()
    {
        $this->user->revokePermissionTo(PermissionsEnum::CREATE_PROJECTS->value);

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
    public function the_working_hours_field_must_be_integer()
    {
        $this->create(['working_hours' => 'test'])
            ->assertSessionHasErrors(['working_hours' => 'The working hours must be an integer.']);
    }

    /**
     * @test
     */
    public function the_working_hours_field_is_required()
    {
        $this->create(['working_hours' => ''])
            ->assertSessionHasErrors(['working_hours' => 'The working hours field is required.']);
    }

    /**
     * @return TestResponse
     */
    public function create($overwtites = [])
    {
        return $this->actingAs($this->user)
            ->post(route('project.store', $this->workspace), array_merge([
                'name' => 'test',
                'working_hours' => 8,
            ], $overwtites));
    }
}

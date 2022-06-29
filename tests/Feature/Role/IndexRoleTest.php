<?php

namespace Tests\Feature\Role;

use App\Enums\PermissionsEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class IndexRoleTest extends TestCase
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
        $this->user->givePermissionTo(PermissionsEnum::SEE_ROLES->value);
    }

    /**
     * @test
     */
    public function user_can_see_roles()
    {
        $adminRole = Role::create([
            'name' => 'Moderator',
            'workspace_id' => $this->user->workspace_id,
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->user->workspace_id);
        $this->user->assignRole($adminRole);

        $this->index()
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Roles/Index')
                ->has('roles.data', 2)
                ->has('roles.data.1', fn (Assert $page) => $page
                    ->where('id', $adminRole->id)
                    ->where('name', $adminRole->name)
                    ->where('users_count', $adminRole->users()->count())
                    ->etc()
                )
            );
    }

    /**
     * @test
     */
    public function user_must_have_permission_to_see_roles()
    {
        $this->user->revokePermissionTo(PermissionsEnum::SEE_ROLES->value);

        $this->index()->assertStatus(403);
    }

    /**
     * @return TestResponse
     */
    public function index()
    {
        return $this->actingAs($this->user)
            ->get(route('role.index', [
                'workspace' => $this->user->workspace_id
            ]));
    }
}

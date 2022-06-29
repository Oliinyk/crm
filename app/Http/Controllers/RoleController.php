<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @return Response
     */
    public function index(Request $request, Workspace $workspace)
    {
        $roles = Role::where('workspace_id', $workspace->id)
            ->with('permissions')
            ->withCount('users')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Roles/Index', [
            'filters' => $request->all('search', 'trashed'),
            'roles' => RoleResource::collection($roles),
            'can' => [
                'delete_roles' => $request->user()->can(PermissionsEnum::DELETE_ROLES->value),
                'add_roles' => $request->user()->can(PermissionsEnum::ADD_ROLES->value),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateRoleRequest $request
     * @param Workspace $workspace
     * @return RedirectResponse
     */
    public function store(CreateRoleRequest $request, Workspace $workspace)
    {
        /**
         * @var Role $role
         */
        $role = Role::create(['name' => $request->name]);

        $role->givePermissionTo($request->permissions);

        return Redirect::route('role.index', $workspace)
            ->with('success', __('Role created.'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRoleRequest $request
     * @param Workspace $workspace
     * @param Role $role
     * @return RedirectResponse
     */
    public function update(UpdateRoleRequest $request, Workspace $workspace, Role $role)
    {
        $role->update([
            'name' => $request->name,
        ]);

        $role->syncPermissions($request->permissions);

        return Redirect::route('role.index', $workspace)
            ->with('success', __('Role updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Workspace $workspace
     * @param Role $role
     * @return RedirectResponse
     */
    public function destroy(Workspace $workspace, Role $role)
    {
        if ($role->users()->count()) {
            return Redirect::back()->with('error', __('Role can not be deleted because it has users.'));
        }

        $role->delete();

        return Redirect::back()->with('success', __('Role deleted.'));
    }
}

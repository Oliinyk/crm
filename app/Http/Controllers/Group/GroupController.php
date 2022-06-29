<?php

namespace App\Http\Controllers\Group;

use App\Enums\PermissionsEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Group\CreateGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\User;
use App\Models\Workspace;
use App\Scopes\ExcludeSelectedOptionsScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Group::class, 'group');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param Group $group
     * @return AnonymousResourceCollection
     */
    public function index(Request $request, Workspace $workspace, Group $group)
    {
        $users = User::orderByName()
            ->memberOfTheWorkspace($workspace)
            ->filter($request->only('search'))
            ->withGlobalScope('exclude-selected-options', new ExcludeSelectedOptionsScope)
            ->whereDoesntHave('groups', fn ($query) => $query->whereId($group->id))
            ->paginate(10);

        return \App\Http\Resources\Search\UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateGroupRequest $request
     * @param Workspace $workspace
     * @return RedirectResponse
     */
    public function store(CreateGroupRequest $request, Workspace $workspace)
    {
        $group = Group::create([
            'name' => $request->get('name'),
            'workspace_id' => $workspace->id,
        ]);

        return Redirect::route('group.show', [
                'group' => $group->id,
                'workspace' => $workspace->id
            ]
        )->with('success', __('Group created.'));
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param Group $group
     * @return Response
     */
    public function show(Request $request, Workspace $workspace, Group $group)
    {
        $users = User::orderByName()
            ->memberOfTheWorkspace($workspace)
            ->filter($request->only('search', 'trashed'))
            ->whereHas('groups', fn ($query) => $query->whereId($group->id))
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Groups/Show', [
            'filters' => $request->all('search', 'trashed'),
            'users' => UserResource::collection($users),
            'groups' => Group::all(),
            'group' => $group,
            'can' => [
                'manage_groups' => $request->user()->can(PermissionsEnum::MANAGE_GROUPS->value),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateGroupRequest $request
     * @param Workspace $workspace
     * @param Group $group
     * @return RedirectResponse
     */
    public function update(UpdateGroupRequest $request, Workspace $workspace, Group $group)
    {
        $group->update($request->safe()->all());

        return Redirect::route('group.show', [
            'workspace' => $workspace->id,
            'group' => $group->id
        ])
            ->with('success', __('Group updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Workspace $workspace
     * @param Group $group
     * @return RedirectResponse
     */
    public function destroy(Workspace $workspace, Group $group)
    {
        /**
         * Detach all members.
         */
        $group->members()->detach();

        /**
         * Delete Group.
         */
        $group->delete();

        return Redirect::route('user.index', $workspace->id)
            ->with('success', __('Group deleted.'));
    }
}

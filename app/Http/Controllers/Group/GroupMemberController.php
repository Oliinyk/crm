<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\GroupMemberRequest;
use App\Models\Group;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class GroupMemberController extends Controller
{
    /**
     * Attach members to a group.
     *
     * @param GroupMemberRequest $request
     * @param Workspace $workspace
     * @param Group $group
     * @return RedirectResponse
     */
    public function store(GroupMemberRequest $request, Workspace $workspace, Group $group)
    {
        $group->members()->attach($request->members);

        return Redirect::route('group.show', ['workspace' => $workspace, 'group' => $group->id])
            ->with('success', __('Users was added to a group successfully!'));
    }

    /**
     * Detach members from a group.
     *
     * @param GroupMemberRequest $request
     * @param Workspace $workspace
     * @param Group $group
     * @return RedirectResponse
     */
    public function destroy(GroupMemberRequest $request, Workspace $workspace, Group $group)
    {
        $group->members()->detach($request->members);

        return Redirect::route('group.show', ['workspace' => $workspace, 'group' => $group->id])
            ->with('success', __('Users was detached fom a group successfully!'));
    }
}

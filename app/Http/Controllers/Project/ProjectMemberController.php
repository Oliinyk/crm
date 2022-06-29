<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\ProjectMemberRequest;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ProjectMemberController extends Controller
{
    /**
     * Attach members to a Project.
     *
     * @param ProjectMemberRequest $request
     * @param Workspace $workspace
     * @param Project $project
     * @return RedirectResponse
     */
    public function store(ProjectMemberRequest $request, Workspace $workspace, Project $project)
    {
        $project->members()->attach($request->members);

        return Redirect::route('project.show', ['workspace' => $workspace->id, 'project' => $project->id])
            ->with('success', __('Users was added to a group successfully!'));
    }

    /**
     * Detach members from a Project.
     *
     * @param ProjectMemberRequest $request
     * @param Workspace $workspace
     * @param Project $project
     * @return RedirectResponse
     */
    public function destroy(ProjectMemberRequest $request, Workspace $workspace, Project $project)
    {
        $project->members()->detach($request->members);

        return Redirect::route('project.show', ['workspace' => $workspace->id, 'project' => $project->id])
            ->with('success', __('Users was detached fom a group successfully!'));
    }
}

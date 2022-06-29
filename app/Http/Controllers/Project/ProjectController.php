<?php

namespace App\Http\Controllers\Project;

use App\Enums\PermissionsEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ProjectController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Project::class, 'project');
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
        $projects = Project::filter($request->all('search'))
            ->when(
                ! $request->user()->can(PermissionsEnum::SEE_ALL_PROJECTS->value),
                function (Builder $query) use ($request) {
                    $query->whereHas('members', function (Builder $query) use ($request) {
                        $query->where('user_id', '=', $request->user()->id);
                    });
                }
            )
            ->with('owner')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Projects/Index', [
            'can' => [
                'create_project' => $request->user()->can(PermissionsEnum::CREATE_PROJECTS->value),
            ],
            'filters' => $request->all('search', 'trashed'),
            'projects' => ProjectResource::collection($projects),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @return RedirectResponse
     */
    public function store(Request $request, Workspace $workspace)
    {
        /**
         * Validate request.
         */
        $request->validate([
            'name' => 'required',
            'working_hours' => 'required|integer',
        ]);

        /**
         * Create project.
         */
        Project::create([
            'name' => $request->name,
            'working_hours' => $request->working_hours,
            'workspace_id' => $workspace->id,
            'owner_id' => $request->user()->id,
        ]);

        return Redirect::route('project.index', $workspace)
            ->with('success', __('Project created.'));
    }

    /**
     * Display the specified resource.
     *
     * @param Project $project
     * @param Workspace $workspace
     * @return Response
     */
    public function show(Workspace $workspace, Project $project)
    {
        return Inertia::render('Projects/Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'working_hours' => $project->working_hours,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param Project $project
     * @return RedirectResponse
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(Request $request, Workspace $workspace, Project $project)
    {
        /**
         * Validate request.
         */
        $request->validate([
            'name' => ['required', 'max:50'],
            'photo' => ['nullable', 'image'],
            'working_hours' => ['required', 'integer'],
        ]);

        /**
         * Update Project.
         */
        $project->update([
            'name' => $request->name,
            'workspace_id' => $workspace->id,
            'owner_id' => $request->user()->id,
        ]);

        /**
         * Add workspace Image.
         */
        if ($request->hasFile('photo')) {
            $project->clearMediaCollection();

            $project->addMediaFromRequest('photo')
                ->toMediaCollection();
        }

        return Redirect::back()->with('success', __('Project updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Workspace $workspace
     * @param Project $project
     * @return RedirectResponse
     */
    public function destroy(Workspace $workspace, Project $project)
    {
        $project->delete();

        return Redirect::route('project.index', $workspace)
            ->with('success', __('Project deleted.'));
    }
}

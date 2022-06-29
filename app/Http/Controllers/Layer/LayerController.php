<?php

namespace App\Http\Controllers\Layer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Layer\CreateLayerRequest;
use App\Http\Requests\Layer\UpdateLayerRequest;
use App\Http\Resources\LayerResource;
use App\Models\Layer;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class LayerController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Layer::class, 'layer');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param Project $project
     * @return Response
     */
    public function index(Request $request, Workspace $workspace, Project $project)
    {
        return Inertia::render('Layers/Index', [
            'filters' => $request->all('search'),
            'layers' => LayerResource::collection($project->layers()
                ->filter($request->all('search'))
                ->paginate(10)
                ->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateLayerRequest $request
     * @param Workspace $workspace
     * @param Project $project
     * @return RedirectResponse
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function store(CreateLayerRequest $request, Workspace $workspace, Project $project)
    {
        /**
         * Create layer.
         */
        $layer = Layer::create([
            'title' => $request->get('title'),
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'author_id' => $request->user()->id,
            'parent_layer_id' => $request->get('parent_layer_id'),
        ]);

        /**
         * Add layer Image
         */
        if ($request->hasFile('image')) {
            $layer->addMediaFromRequest('image')->toMediaCollection();
        }

        return Redirect::route('ticket.index', [
            'workspace' => $workspace->id,
            'project' => $project->id
        ])->with('success', __('Ticket created.'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLayerRequest $request
     * @param Workspace $workspace
     * @param Project $project
     * @param Layer $layer
     * @return RedirectResponse
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(UpdateLayerRequest $request, Workspace $workspace, Project $project, Layer $layer)
    {
        /**
         * Update layer.
         */
        $layer->update([
            'title' => $request->get('title'),
            'parent_layer_id' => $request->get('parent_layer_id'),
        ]);

        /**
         * Add layer Image.
         */
        if ($request->hasFile('image')) {
            $layer->clearMediaCollection();
            $layer->addMediaFromRequest('image')->toMediaCollection();
        }

        return Redirect::route('ticket.index', [
            'project' => $project,
            'workspace' => $workspace
        ])->with('success', __('Ticket created.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Workspace $workspace
     * @param Project $project
     * @param Layer $layer
     * @return RedirectResponse
     */
    public function destroy(Workspace $workspace, Project $project, Layer $layer)
    {
        /**
         * Delete layer.
         */
        $layer->delete();

        return Redirect::route('ticket.index', [
            'workspace' => $workspace->id,
            'project' => $project->id,
        ])->with('success', __('Ticket created.'));
    }
}

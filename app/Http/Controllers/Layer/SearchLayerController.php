<?php

namespace App\Http\Controllers\Layer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Search\LayerResource;
use App\Models\Layer;
use App\Models\Project;
use App\Models\Workspace;
use App\Scopes\ExcludeSelectedOptionsScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchLayerController extends Controller
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
     * @return AnonymousResourceCollection
     */
    public function index(Request $request, Workspace $workspace, Project $project)
    {
        $layers = $project->layers()
            ->withGlobalScope('exclude-selected-options', new ExcludeSelectedOptionsScope)
            ->filter($request->all('search'))
            ->paginate(10);

        return LayerResource::collection($layers);
    }
}

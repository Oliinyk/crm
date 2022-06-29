<?php

namespace App\Http\Controllers\TicketType;

use App\Http\Controllers\Controller;
use App\Http\Resources\Search\TicketTypeResource;
use App\Models\Project;
use App\Models\TicketType;
use App\Models\Workspace;
use App\Scopes\ExcludeSelectedOptionsScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchTicketTypeController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(TicketType::class, 'ticketType');
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
        $ticketTypes = TicketType::withGlobalScope('exclude-selected-options', new ExcludeSelectedOptionsScope)
            ->filter($request->all('search'))
            ->paginate(10)
            ->withQueryString();

        return TicketTypeResource::collection($ticketTypes);
    }
}

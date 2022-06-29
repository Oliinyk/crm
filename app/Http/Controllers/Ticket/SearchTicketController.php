<?php

namespace App\Http\Controllers\Ticket;

use App\Enums\PermissionsEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Search\TicketResource;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\Workspace;
use App\Scopes\ExcludeSelectedOptionsScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchTicketController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Ticket::class, 'ticket');
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
        $tickets = Ticket::with('ticketFields')
            ->withGlobalScope('exclude-selected-options', new ExcludeSelectedOptionsScope)
            ->when($request->user()->can(PermissionsEnum::SEE_JOINED_TICKETS->value), function ($query) {
                $query->where('author_id', auth()->id())
                    ->orWhere('assignee_id', auth()->id());
            })
            ->where('project_id', '=', $project->id)
            ->filter($request->all('search'))
            ->paginate(10)
            ->withQueryString();

        return TicketResource::collection($tickets);
    }
}

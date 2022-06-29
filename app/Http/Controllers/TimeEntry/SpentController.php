<?php

namespace App\Http\Controllers\TimeEntry;

use App\Enums\TimeEntryTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeEntryRequest;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TimeEntryResource;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TimeEntry;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class SpentController extends Controller
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
     * @return Response
     */
    public function index(Request $request, Workspace $workspace, Project $project, Ticket $ticket)
    {
        $timeEntries = $ticket->timeEntries()
            ->where('type', '=', TimeEntryTypeEnum::SPENT->value)
            ->paginate(3, ['*'], 'logger-page')
            ->appends(Arr::except($request->query(), 'logger-page'));

        return Inertia::modal('LoggerModal', [
            'ticket' => new TicketResource($ticket->load('ticketFields')),
            'timeSpent' => [
                'time_spent' => $ticket->time_spent,
                'time_estimate' => $ticket->time_estimate,
                'time_remaining' => $ticket->time_remaining,
                'time_percent' => $ticket->time_percent,
                'logger' => TimeEntryResource::collection($timeEntries)->response()->getData()
            ],
            'width' => 708
        ])->basePageRoute('ticket.show', array_merge([
            'workspace' => $workspace,
            'project' => $project,
            'ticket' => $ticket
        ], $request->query()));
    }

    /**
     * Display a listing of the resource.
     *
     * @param StoreTimeEntryRequest $request
     * @param Workspace $workspace
     * @param Project $project
     * @param Ticket $ticket
     * @return RedirectResponse
     */
    public function store(StoreTimeEntryRequest $request, Workspace $workspace, Project $project, Ticket $ticket)
    {
        TimeEntry::create($request->all());

        return Redirect::route('ticket.time-spent.index',
            array_merge([
                'project' => $project,
                'ticket' => $ticket,
                'workspace' => $workspace
            ], $request->query()))
            ->with('success', __('Ticket updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param Project $project
     * @param Ticket $ticket
     * @param TimeEntry $timeEntry
     * @return RedirectResponse
     */
    public function destroy(
        Request $request,
        Workspace $workspace,
        Project $project,
        Ticket $ticket,
        TimeEntry $timeSpent
    ) {
        $timeSpent->delete();

        return Redirect::route('ticket.time-spent.index',
            array_merge([
                'project' => $project,
                'ticket' => $ticket,
                'workspace' => $workspace
            ], $request->query()))
            ->with('success', __('Ticket updated.'));
    }
}

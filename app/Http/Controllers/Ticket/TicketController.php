<?php

namespace App\Http\Controllers\Ticket;

use App\Enums\PermissionsEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Http\Requests\Ticket\IndexTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketTypeResource;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class TicketController extends Controller
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
     * @param IndexTicketRequest $request
     * @param Workspace $workspace
     * @param Project $project
     * @return Response
     */
    public function index(IndexTicketRequest $request, Workspace $workspace, Project $project)
    {
        $filters = $request->all([
            'search',
            'author_id',
            'status',
            'priority',
            'assignee',
            'layer',
            'start_date',
            'due_date',
            'watchers',
            'types',
            'updated_at',
            'changed',
            'progress',
            'selected',
        ]);

        $tickets = Ticket::with('ticketFields')
            ->orderBy($request->orderBy ?? 'id',$request->direction ?? 'asc')
            ->where('project_id', '=', $project->id)
            ->whereNull('parent_ticket_id')
            ->when($request->user()->can(PermissionsEnum::SEE_JOINED_TICKETS->value), function ($query) {
                $query->where('author_id', auth()->id())
                    ->orWhere('assignee_id', auth()->id());
            })
            ->filter($filters)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Tickets/Index', [
            'filters' => fn () => $filters,
            'tickets' => fn () => TicketResource::collection($tickets),
            'project' => fn () => $project,
            'ticketTypes' => fn () => TicketTypeResource::collection(TicketType::all()),
            'fields' => fn () => TicketField::tableFields($request->user()->id),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTicketRequest $request
     * @param Workspace $workspace
     * @param Project $project
     * @return RedirectResponse
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function store(CreateTicketRequest $request, Workspace $workspace, Project $project)
    {
        /**
         * Create ticket.
         */
        $ticket = Ticket::create([
            'title' => $request->title,
            'ticket_type_id' => $request->ticket_type,
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'author_id' => $request->user()->id,
        ]);

        /**
         * Add the Ticket's media.
         */
        if ($request->has('media')) {
            foreach ($request->media as $image) {
                $ticket->addMediaFromDisk($image['url'], 'public')->toMediaCollection();
            }
        }
        /**
         * Create ticket fields.
         */
        foreach ($request->fields as $field) {
            $ticket->ticketFields()
                ->create($field);
        }

        return Redirect::route('ticket.index', [
            'project' => $project,
            'workspace' => $workspace
        ])->with('success', __('Ticket created.'));
    }

    /**
     * @param Request $request
     * @param Workspace $workspace
     * @param Project $project
     * @param Ticket $ticket
     * @return mixed
     */
    public function show(Request $request, Workspace $workspace, Project $project, Ticket $ticket)
    {
        return Inertia::modal('TicketDetailsModal', [
            'ticket' => new TicketResource($ticket->load('ticketFields', 'parentTicket')),
            'width' => 957
        ])->basePageRoute('ticket.index', array_merge([
            'workspace' => $workspace,
            'project' => $project
        ], $request->query()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTicketRequest $request
     * @param Workspace $workspace
     * @param Project $project
     * @param Ticket $ticket
     * @return RedirectResponse
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(UpdateTicketRequest $request, Workspace $workspace, Project $project, Ticket $ticket)
    {
        if ($request->has('id')) {
            $ticket->ticketFields()
                ->findOrFail($request->id)
                ->update(['value' => $request->value]);
        }

        if ($request->has('title')) {
            $ticket->update(['title' => $request->title]);
        }

        /**
         * Update the Ticket's media.
         */
        if ($request->has('media')) {
            $requestCollection = collect($request->media)->keyBy('id');
            $ticketMediaCollection = $ticket->getMedia()->keyBy('id');

            /**
             * Add
             *
             * Get new items from request
             * Add these items to the model
             */
            $newItems = $requestCollection->diffKeys($ticketMediaCollection);

            foreach ($newItems as $media) {
                $ticket->addMediaFromDisk($media['url'], 'public')->preservingOriginal()->toMediaCollection();
            }

            /**
             * Remove
             */
            $itemsToDelete = $ticketMediaCollection->diffKeys($requestCollection);
            $itemsToDelete->each->delete();

            /**
             * Activity log
             */
            activity()
                ->causedBy(auth()->user())
                ->performedOn($ticket)
                ->event('updated')
                ->withProperties([
                    'type' => 'attachments',
                    'name' => 'attachments',
                    'old' => $itemsToDelete->pluck('name'),
                    'new' => $newItems->pluck('name'),
                ])
                ->log('updated');
        }

        return Redirect::route('ticket.show',
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
     * @param Project $project
     * @param Workspace $workspace
     * @param Ticket $ticket
     * @return RedirectResponse
     */
    public function destroy(Workspace $workspace, Project $project, Ticket $ticket)
    {
        /**
         * Delete ticket fields.
         */
        $ticket->ticketFields()->delete();

        /**
         * Delete ticket.
         */
        $ticket->delete();

        return Redirect::route('ticket.index', [
            'project' => $project,
            'workspace' => $workspace
        ])->with('success', __('Ticket deleted.'));
    }
}

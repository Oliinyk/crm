<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketField;
use App\Models\Workspace;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ChildTicketController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTicketRequest $request
     * @param Workspace $workspace
     * @param Project $project
     * @param Ticket $ticket
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(CreateTicketRequest $request, Workspace $workspace, Project $project, Ticket $ticket)
    {
        $this->authorize('create', Ticket::class);

        /**
         * Create a child ticket.
         */
        $childTicket = Ticket::create([
            'title' => $request->title,
            'ticket_type_id' => $request->ticket_type,
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'author_id' => $request->user()->id,
            'parent_ticket_id' => $ticket->id
        ]);

        /**
         * Create child ticket fields.
         */
        foreach ($request->fields as $field) {
            $childTicket->ticketFields()
                ->create($field);
        }

        /**
         * Record Activity
         */
        activity()
            ->causedBy(auth()->user())
            ->performedOn($ticket)
            ->event('updated')
            ->withProperties([
                'type' => TicketField::TYPE_CHILD_TICKET,
                'name' => TicketField::TYPE_CHILD_TICKET,
                'old' => null,
                'new' => $request->title,
            ])
            ->log('updated');

        return Redirect::route('ticket.show', [
            'workspace' => $workspace,
            'project' => $project,
            'ticket' => $ticket->id
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Workspace $workspace
     * @param Project $project
     * @param Ticket $ticket
     * @param Ticket $child
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(Workspace $workspace, Project $project, Ticket $ticket, Ticket $child)
    {
        $this->authorize('delete', $child);
        /**
         * Delete ticket fields.
         */
        $child->ticketFields()->delete();

        /**
         * Delete ticket.
         */
        $child->delete();

        /**
         * Record Activity
         */
        activity()
            ->causedBy(auth()->user())
            ->performedOn($ticket)
            ->event('deleted')
            ->withProperties([
                'type' => TicketField::TYPE_CHILD_TICKET,
                'name' => TicketField::TYPE_CHILD_TICKET,
                'old' => null,
                'new' => $child->title,
            ])
            ->log('updated');

        return Redirect::route('ticket.show', [
            'project' => $project,
            'workspace' => $workspace,
            'ticket' => $ticket,
        ])->with('success', __('Ticket deleted.'));
    }
}

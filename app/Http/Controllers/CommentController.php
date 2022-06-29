<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\Workspace;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Redirect;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCommentRequest $request
     * @param Workspace $workspace
     * @param Project $project
     * @param Ticket $ticket
     * @return RedirectResponse
     */
    public function store(StoreCommentRequest $request, Workspace $workspace, Project $project, Ticket $ticket)
    {
        $ticket->comments()->create([
            'text' => $request->text,
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'author_id' => $request->user()->id
        ]);

        return Redirect::route('ticket.show', [
            'workspace' => $workspace,
            'project' => $project,
            'ticket' => $ticket
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Workspace $workspace
     * @param Project $project
     * @param Ticket $ticket
     * @param Comment $comment
     * @return Application|ResponseFactory|Response
     */
    public function destroy(Workspace $workspace, Project $project, Ticket $ticket, Comment $comment)
    {
        $comment->delete();

        return \response('OK');
    }
}

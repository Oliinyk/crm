<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\Workspace;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class TicketTableController extends Controller
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
     * @return Application|ResponseFactory|Response
     */
    public function index(Request $request,Workspace $workspace, Project $project)
    {
        Cache::forget("user.{$request->user()->id}.tickets.table");
        Cache::rememberForever("user.{$request->user()->id}.tickets.table", fn () => $request->all());

        return response('ok');
    }
}

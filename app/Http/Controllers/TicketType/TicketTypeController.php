<?php

namespace App\Http\Controllers\TicketType;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketType\CreateTicketTypeRequest;
use App\Http\Requests\TicketType\UpdateTicketTypeRequest;
use App\Http\Resources\TicketTypeResource;
use App\Models\TicketType;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class TicketTypeController extends Controller
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
     * Get the map of resource methods to ability names.
     *
     * @return array
     */
    protected function resourceAbilityMap()
    {
        return [
            'index' => 'viewAny',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'destroy' => 'delete',
            'forceDestroy' => 'forceDeleted',
            'restore' => 'restored',
        ];
    }

    /**
     * Get the list of resource methods which do not have model parameters.
     *
     * @return array
     */
    protected function resourceMethodsWithoutModels()
    {
        return ['index', 'create', 'store', 'destroy'];
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
        $ticketTypes = TicketType::filter($request->all('search'))
            ->withTrashed()
            ->with('author', 'ticketFields')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('TicketTypes/Index', [
            'filters' => $request->all('search'),
            'ticketTypes' => TicketTypeResource::collection($ticketTypes),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTicketTypeRequest $request
     * @param Workspace $workspace
     * @return RedirectResponse
     */
    public function store(CreateTicketTypeRequest $request, Workspace $workspace)
    {
        /**
         * Create Ticket Type.
         */
        $ticketType = TicketType::create([
            'name' => $request->get('name'),
            'title' => $request->get('title'),
            'workspace_id' => $workspace->id,
            'author_id' => $request->user()->id,
        ]);

        /**
         * Attach all fields to the Ticket Type.
         */
        $ticketType->ticketFields()->createMany($request->fields);

        return Redirect::route('ticket-type.index', $workspace)
            ->with('success', __('Ticket Type created.'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTicketTypeRequest $request
     * @param Workspace $workspace
     * @param TicketType $ticketType
     * @return RedirectResponse
     */
    public function update(UpdateTicketTypeRequest $request, Workspace $workspace, TicketType $ticketType)
    {
        /**
         * Update Ticket Type.
         */
        $ticketType->update([
            'name' => $request->get('name'),
            'title' => $request->get('title'),
        ]);

        /**
         * Delete old Ticket Type fields.
         */
        $ticketType->ticketFields()->delete();

        /**
         * Attach all fields to the Ticket Type.
         */
        $ticketType->ticketFields()->createMany($request->fields);

        return Redirect::route('ticket-type.index', $workspace)
            ->with('success', __('Ticket Type updated.'));
    }

    /**
     *  Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @return RedirectResponse
     */
    public function destroy(Request $request, Workspace $workspace)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:ticket_types,id',
        ]);

        TicketType::destroy($request->ids);

        return Redirect::route('ticket-type.index', array_merge(['workspace' => $workspace], $request->query()))
            ->with('success', __('Ticket Type disabled.'));
    }

    /**
     * Force Remove the specified resource from storage.
     *
     * @param Workspace $workspace
     * @param TicketType $ticketType
     * @return RedirectResponse
     */
    public function forceDestroy(Workspace $workspace, TicketType $ticketType)
    {
        /**
         * Delete all ticket fields.
         */
        $ticketType->ticketFields()->delete();

        /**
         * Delete Ticket Type.
         */
        $ticketType->forceDelete();

        return Redirect::route('ticket-type.index', $workspace)
            ->with('success', __('Ticket Type deleted.'));
    }

    /**
     * Restore the specified resource to storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param TicketType $ticketType
     * @return RedirectResponse
     */
    public function restore(Request $request, Workspace $workspace, TicketType $ticketType)
    {
        $ticketType->restore();

        return Redirect::route('ticket-type.index', array_merge(['workspace' => $workspace], $request->query()))
            ->with('success', __('Ticket Type restored.'));
    }
}

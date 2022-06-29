<?php

namespace App\Http\Controllers\TicketType;

use App\Http\Controllers\Controller;
use App\Models\TicketField;
use App\Models\TicketType;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class CopyTicketTypeController extends Controller
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
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param TicketType $ticketType
     * @return RedirectResponse
     */
    public function store(Request $request, Workspace $workspace, TicketType $ticketType)
    {
        /**
         * Copy Ticket Type.
         */
        $newTicketType = $ticketType->replicate()
            ->fill([
                'name' => $ticketType->generateNewName(),
            ]);

        /**
         * Save new ticket type.
         */
        $newTicketType->save();

        /**
         * Copy ticket type fields.
         */
        $ticketType->ticketFields
            ->each(function (TicketField $ticketField) use ($newTicketType) {
                $ticketField->replicate()
                    ->fill(['ticket_field_id' => $newTicketType->id])
                    ->save();
            });

        return Redirect::route('ticket-type.index', $workspace)
            ->with('success', __('Ticket Type copied.'));
    }
}

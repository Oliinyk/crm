<?php

namespace App\Http\Controllers\Client;

use App\Enums\PermissionsEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CreateClientRequest;
use App\Http\Requests\Client\DestroyManyClientsRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Client::class, 'client');
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
        $clients = Client::filter($request->all('search'))
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'filters' => $request->all('search'),
            'clients' => ClientResource::collection($clients),
            'can' => [
                'delete_clients' => $request->user()->can(PermissionsEnum::DELETE_CLIENTS->value),
                'add_clients' => $request->user()->can(PermissionsEnum::ADD_CLIENTS->value),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateClientRequest $request
     * @param Workspace $workspace
     * @return RedirectResponse
     */
    public function store(CreateClientRequest $request, Workspace $workspace)
    {
        Client::create([
            'name' => $request->get('name'),
            'status' => $request->get('status'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'city' => $request->get('city'),
            'workspace_id' => $workspace->id,
        ]);

        return Redirect::route('client.index', $workspace->id)
            ->with('success', __('Client created.'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateClientRequest $request
     * @param Workspace $workspace
     * @param Client $client
     * @return RedirectResponse
     */
    public function update(UpdateClientRequest $request, Workspace $workspace, Client $client)
    {
        $client->update([
            'name' => $request->get('name'),
            'status' => $request->get('status'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'city' => $request->get('city'),
        ]);

        return Redirect::route('client.index', $workspace->id)
            ->with('success', __('Client updated.'));
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param DestroyManyClientsRequest $request
     * @param Workspace $workspace
     * @return RedirectResponse
     */
    public function destroy(DestroyManyClientsRequest $request, Workspace $workspace)
    {
        $result = Client::destroy($request->ids);

        return Redirect::route('client.index', $workspace->id)
            ->with('success', __('Clients deleted', ['count' => $result]));
    }
}

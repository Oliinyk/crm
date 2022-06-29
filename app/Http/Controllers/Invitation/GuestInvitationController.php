<?php

namespace App\Http\Controllers\Invitation;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvitationResource;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class GuestInvitationController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param Invitation $invitation
     * @return Response
     */
    public function show(Request $request, Workspace $workspace, Invitation $invitation)
    {
        return Inertia::render('Auth/Invitation', [
            'invitation' => new InvitationResource($invitation),
        ]);
    }

    /**
     * Accept the guest invitation.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param Invitation $invitation
     * @return RedirectResponse
     */
    public function update(Request $request, Workspace $workspace, Invitation $invitation)
    {
        /**
         * Validate request.
         */
        $request->validate([
            'full_name' => ['required', 'max:50'],
            'password' => ['required', 'min:8', 'max:50'],
        ]);

        /**
         * Create the User with the details from the invitation.
         */
        $user = User::create([
            'email' => $invitation->email,
            'workspace_id' => $invitation->workspace_id,
            'full_name' => $request->get('full_name'),
            'password' => Hash::make($request->get('password')),
        ]);

        /**
         *  Delete the invitation, so it can't be used again.
         */
        $invitation->delete();

        /**
         * Attach the user as a member to the workspace.
         */
        $invitation->workspace->members()->attach($user->id);

        /**
         * Log the user.
         */
        Auth::login($user);

        return Redirect::intended(RouteServiceProvider::HOME);
    }
}

<?php

namespace App\Http\Controllers\Invitation;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateInvitationRequest;
use App\Http\Resources\InvitationResource;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\InvitationNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserInvitationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param CreateInvitationRequest $request
     * @param Workspace $workspace
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(CreateInvitationRequest $request, Workspace $workspace)
    {
        $this->authorize('create', Invitation::class);

        /**
         * Generate token.
         */
        do {
            $token = Str::random(20);
        } while (Invitation::where('token', $token)->exists());

        /**
         * Create invitation.
         */
        $invitation = Invitation::updateOrCreate([
            'workspace_id' => $workspace->id,
            'email' => $request->get('email'),
        ], [
            'author_id' => $request->user()->id,
            'token' => $token,
        ]);

        /**
         * Send notification about invitation.
         */
        Notification::route('mail', $request->get('email'))
            ->notify(new InvitationNotification($invitation));

        return Redirect::route('user.index', $workspace->id)
            ->with('success', __('User invited.'));
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param Invitation $invitation
     * @return RedirectResponse|Response
     */
    public function show(Request $request, Workspace $workspace, Invitation $invitation)
    {
        $user = User::firstWhere('email', $invitation->email);

        if ($user) {

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

            return Redirect::route('dashboard', $user->workspace_id)
                ->with('info', __('Check your Invitations.'));
        }

        return Inertia::render('Auth/Invitation', [
            'invitation' => new InvitationResource($invitation),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param $token
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Workspace $workspace, $token)
    {
        $invitation = Invitation::whereToken($token)->firstOrFail();

        $this->authorize('delete', $invitation);

        /**
         * Check if auth user exists in the invitation workspace.
         */
        if ($invitation
            ->workspace
            ->members()
            ->where('user_id', '=', $request->user()->id)
            ->exists()) {
            $invitation->delete();
        }

        return Redirect::route('user.index', $workspace->id)
            ->with('success', __('Invitation deleted.'));
    }
}

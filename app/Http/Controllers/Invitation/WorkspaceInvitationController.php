<?php

namespace App\Http\Controllers\Invitation;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Workspace;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class WorkspaceInvitationController extends Controller
{
    /**
     * Accept the user invitation.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param $token
     * @return RedirectResponse
     */
    public function update(Request $request, Workspace $workspace, $token)
    {
        $invitation = Invitation::whereToken($token)->firstOrFail();

        /**
         * Attach the user as a member to the workspace.
         */
        $invitation->workspace->members()->attach($request->user()->id);

        /**
         *  Delete the invitation, so it can't be used again.
         */
        $invitation->delete();

        return Redirect::intended(RouteServiceProvider::HOME);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param $token
     * @return RedirectResponse
     */
    public function destroy(Request $request, Workspace $workspace, $token)
    {
        $invitation = Invitation::whereToken($token)->firstOrFail();

        if ($request->user()->email == $invitation->email) {
            $invitation->delete();
        }

        return Redirect::back()->with('success', 'OK');
    }
}

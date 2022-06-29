<?php

namespace App\Http\Controllers\User;

use App\Enums\PermissionsEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\InvitationResource;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @return Response
     */
    public function index(Request $request, Workspace $workspace)
    {
        $users = User::orderByName()
            ->memberOfTheWorkspace($workspace)
            ->with('roles')
            ->filter($request->only('search', 'trashed'))
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'filters' => $request->all('search', 'trashed'),
            'users' => UserResource::collection($users),
            'invitations' => $request->user()->can(PermissionsEnum::MANAGE_INVITATIONS->value) ?
                InvitationResource::collection(Invitation::paginate()) : InvitationResource::collection([]),
            'groups' => Group::all(),
            'can' => [
                'manage_groups' => $request->user()->can(PermissionsEnum::MANAGE_GROUPS->value),
                'manage_invitations' => $request->user()->can(PermissionsEnum::MANAGE_INVITATIONS->value),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @param User $user
     * @return RedirectResponse
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(Request $request, Workspace $workspace, User $user)
    {
        /**
         * Validate request.
         */
        $request->validate([
            'full_name' => ['required', 'max:50'],
            'image' => ['nullable'],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'min:8', 'max:50'],
        ]);

        /**
         * Update the User.
         */
        $user->update($request->only('full_name', 'email', 'locale'));

        /**
         * Update the User's password.
         */
        if ($request->get('password')) {
            $user->update(['password' => Hash::make($request->get('password'))]);
        }

        /**
         * Update the User's media.
         */
        if ($request->has('image')) {

            /**
             * Delete the User Image.
             */
            if (is_null($request->image)) {
                $user->clearMediaCollection();
            } elseif ($request->image['id'] != $user->getFirstMedia()?->id) {
                /**
                 * Update the User Image.
                 */
                $user->clearMediaCollection();

                $user->addMediaFromDisk($request->image['url'], 'public')->preservingOriginal()->toMediaCollection();
            }
        }

        return Redirect::back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return Redirect::back()->with('success', 'User deleted.');
    }

    public function restore(User $user)
    {
        $user->restore();

        return Redirect::back()->with('success', 'User restored.');
    }
}

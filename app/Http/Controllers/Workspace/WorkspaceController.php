<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Rules\FileExtensionsRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class WorkspaceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function store(Request $request)
    {
        /**
         * Validate request data.
         */
        $request->validate([
            'name' => ['required', 'max:50'],
            'photo' => [new FileExtensionsRule(['jpg', 'png'])],
            'plan' => ['required', 'max:50'],
        ]);

        /**
         * Create workspace
         */
        $workspace = Workspace::create([
            'name' => $request->get('name'),
            'plan' => $request->get('plan'),
            'owner_id' => $request->user()->id,
        ]);

        /**
         * Add the Workspace photo.
         */
        if ($request->has('photo') && ! is_null($request->photo)) {
            $workspace->addMediaFromDisk($request->photo['url'], 'public')
                ->preservingOriginal()
                ->toMediaCollection();
        }

        return Redirect::route('dashboard', $workspace->id)
            ->with('success', __('Workspace created.'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @return RedirectResponse
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(Request $request, Workspace $workspace)
    {
        /**
         * Validate request data.
         */
        $request->validate([
            'name' => ['required', 'max:50'],
            'photo' => [new FileExtensionsRule(['jpg', 'png'])],
            'plan' => ['required', 'max:50'],
        ]);

        /**
         * Update workspace.
         */
        $workspace->update($request->only('name', 'plan'));

        /**
         * Update the Workspace photo.
         */
        if ($request->has('photo')) {

            /**
             * Delete the Workspace photo.
             */
            if (is_null($request->photo)) {
                $workspace->clearMediaCollection();
            } elseif ($request->photo['id'] != $workspace->getFirstMedia()?->id) {
                /**
                 * Update the Workspace photo.
                 */
                $workspace->clearMediaCollection();

                $workspace->addMediaFromDisk($request->photo['url'], 'public')
                    ->preservingOriginal()
                    ->toMediaCollection();
            }
        }

        return Redirect::back()->with('success', __('Workspace updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Workspace $workspace
     * @return RedirectResponse
     */
    public function destroy(Workspace $workspace)
    {
        /**
         * Check if the User has 2 more workspaces.
         */
        $validator = Validator::make(
            ['workspace_count' => auth()->user()->workspaces()->count()],
            ['workspace_count' => 'numeric|min:2']
        );

        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput();
        }

        /**
         * Delete workspace.
         */
        $workspace->delete();

        /**
         * Update the user's current workspace.
         */
        auth()->user()->update([
            'workspace_id' => auth()->user()->workspaces->first()->id,
        ]);

        return Redirect::route('dashboard', auth()->user()->workspace_id)
            ->with('success', __('Workspace deleted.'));
    }
}

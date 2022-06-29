<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Search\UserResource;
use App\Models\User;
use App\Models\Workspace;
use App\Scopes\ExcludeSelectedOptionsScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @return AnonymousResourceCollection
     */
    public function index(Request $request, Workspace $workspace)
    {
        $users = User::orderByName()
            ->memberOfTheWorkspace($workspace)
            ->withGlobalScope('exclude-selected-options', new ExcludeSelectedOptionsScope)
            ->with('roles')
            ->filter($request->only('search', 'project_id'))
            ->paginate(10)
            ->withQueryString();

        return UserResource::collection($users);
    }
}

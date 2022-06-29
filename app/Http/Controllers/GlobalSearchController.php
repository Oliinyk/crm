<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Http\Resources\ClientResource;
use App\Http\Resources\LayerResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketTypeResource;
use App\Http\Resources\UserResource;
use App\Models\Client;
use App\Models\Layer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class GlobalSearchController extends Controller
{
    /**
     * Searchable models.
     *
     * @var string[]
     */
    protected $models = [
        User::class => UserResource::class,
        Client::class => ClientResource::class,
        Layer::class => LayerResource::class,
        Project::class => ProjectResource::class,
        Ticket::class => TicketResource::class,
        TicketType::class => TicketTypeResource::class,
    ];

    /**
     * Global search.
     *
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function search(Request $request, Workspace $workspace)
    {
        $results = collect([]);

        /**
         * @var Model $model
         * @var JsonResponse $resource
         */
        foreach ($this->models as $model => $resource) {

            /**
             * Check permissions.
             */
            if ($request->user()->cannot('viewAny', $model)) {
                continue;
            }

            /**
             * @var Collection $result
             */
            $result = $model::filter($request->only('search'))
                ->when($model == User::class, function (Builder $query) use ($workspace) {
                    $query->memberOfTheWorkspace($workspace);
                })
                ->when($model == Project::class, function (Builder $query) use ($request) {
                    $query->when(
                        ! $request->user()->can(PermissionsEnum::SEE_ALL_PROJECTS->value),
                        function (Builder $query) use ($request) {
                            $query->whereHas('members', function (Builder $query) use ($request) {
                                $query->where('user_id', '=', $request->user()->id);
                            });
                        }
                    );
                })
                ->when($model == Ticket::class, function (Builder $query) use ($request) {
                    $query->when(
                        ! $request->user()->can(PermissionsEnum::SEE_ALL_TICKETS->value),
                        function ($query) use ($request) {
                            $query->where('author_id', $request->user()->id)
                                ->orWhere('assignee_id', $request->user()->id);
                        });
                })
                ->limit(10)
                ->get();

            if ($result->isNotEmpty()) {
                $results->put($model, $resource::collection($result));
            }
        }

        return response($results);
    }
}

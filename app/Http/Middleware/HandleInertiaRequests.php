<?php

namespace App\Http\Middleware;

use App\Enums\PermissionsEnum;
use App\Http\Resources\UserResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param Request $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param Request $request
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'auth' => function () use ($request) {
                return $request->user() ? [
                    'user' => new UserResource($request->user()->load(
                        'workspaces', 'invitations', 'roles', 'notifications'
                    )),
                    'topMenu' => $this->topMenu($request),
                    'projectMenu' => $this->projectMenu($request),
                    'workspace_id' => $request->workspace->id,
                ] : null;
            },
            'flash' => function () use ($request) {
                return [
                    'success' => fn () => $request->session()->get('success'),
                    'info' => fn () => $request->session()->get('info'),
                    'warning' => fn () => $request->session()->get('warning'),
                    'error' => fn () => $request->session()->get('error'),
                ];
            },
            'locale' => fn () => app()->getLocale(),
        ]);
    }

    /**
     * The top menu.
     *
     * @param Request $request
     * @return mixed
     */
    public function topMenu(Request $request)
    {
        return
            collect([
                [
                    'name' => __('Dashboard'),
                    'link' => route('dashboard', $request->user()->workspace_id),
                    'isActive' => $request->routeIs('dashboard'),
                    'children' => [],
                ],
            ])->when($request->user()->can('viewAny', Project::class), function ($collection) use ($request) {
                return $collection->push($this->projects($request));
            })->push([
                'name' => __('People'),
                'link' => '',
                'isActive' => $request->is('people*'),
                'children' => collect([
                    [
                        'name' => __('Users'),
                        'isActive' => $request->routeIs('user.index'),
                        'link' => route('user.index', $request->user()->workspace_id),
                    ]
                ])->when($request->user()->can(PermissionsEnum::SEE_CLIENTS->value),
                    function ($collection) use ($request) {
                        return $collection->push([
                            'name' => __('Clients'),
                            'isActive' => $request->routeIs('client.index'),
                            'link' => route('client.index', $request->user()->workspace_id),
                        ]);
                    })->when($request->user()->can(PermissionsEnum::SEE_ROLES->value),
                    function ($collection) use ($request) {
                        return $collection->push([
                            'name' => 'separator-2',
                            'link' => '',
                            'isActive' => false,
                            'separator' => true,
                        ])->push([
                            'name' => __('Roles'),
                            'link' => route('role.index', $request->user()->workspace_id),
                            'isActive' => $request->routeIs('role.index'),
                        ]);
                    }),
            ])->push([
                'name' => __('Templates'),
                'link' => '',
                'isActive' => $request->is('templates*'),
                'children' => collect([
                    [
                        'name' => __('Reports'),
                        'link' => '',
                        'isActive' => false,
                    ]
                ])->when($request->user()->can(PermissionsEnum::MANAGE_TICKET_TYPES->value),
                    function ($collection) use ($request) {
                        return $collection->push([
                            'routeName' => '',
                            'name' => __('Ticket types'),
                            'link' => route('ticket-type.index', $request->user()->workspace_id),
                            'isActive' => $request->routeIs('ticket-type.index'),
                        ]);
                    }),
            ]);
    }

    /**
     * All projects that are available for the User.
     *
     * @param Request $request
     * @return array
     */
    public function projects(Request $request)
    {
        $projects = $request->user()
            ->projects();

        if ($request->user()->can(PermissionsEnum::SEE_JOINED_PROJECTS->value)) {
            $projects = $request->workspace->projects();
        }

        return [
            'name' => __('Projects'),
            'link' => '',
            'isActive' => $request->is('project*'),
            'children' => $projects
                ->limit(5)
                ->get()
                ->map(function (Project $project) use ($request) {
                    return [
                        'name' => $project->name,
                        'link' => route('ticket.index', [
                            'workspace' => $request->workspace->id,
                            'project' => $project->id
                        ]),
                        'isActive' => $request->fullUrlIs(route('ticket.index', [
                            'workspace' => $request->workspace->id,
                            'project' => $project->id
                        ])),
                    ];
                })
                ->whenNotEmpty(function (Collection $collection) {
                    return $collection->push([
                        'name' => 'separator 1',
                        'link' => '',
                        'isActive' => false,
                        'separator' => true,
                    ]);
                })
                ->when($request->user()->can(PermissionsEnum::CREATE_PROJECTS->value),
                    function ($collection) use ($request) {
                        return $collection->push([
                            'name' => __('Create Project'),
                            'link' => '',
                            'isActive' => false,
                            'createProjectModal' => true,
                        ]);
                    }
                )
                ->push([
                    'name' => __('All Projects'),
                    'isActive' => $request->routeIs('project.index'),
                    'link' => route('project.index', $request->workspace->id),
                ]),
        ];
    }

    /**
     * The Project menu.
     *
     * @param Request $request
     * @return mixed
     */
    public function projectMenu(Request $request)
    {
        if (! $request->project) {
            return [];
        }

        return collect([
            [
                'name' => __('Tickets'),
                'link' => route('ticket.index', [
                    'workspace' => $request->workspace->id,
                    'project' => $request->project->id
                ]),
                'isActive' => $request->routeIs('ticket.index'),
                'children' => [],
            ]
        ]);
    }
}

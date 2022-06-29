<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class SetWorkspacePermission
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            app(PermissionRegistrar::class)
                ->setPermissionsTeamId($request->workspace);
        }

        return $next($request);
    }
}

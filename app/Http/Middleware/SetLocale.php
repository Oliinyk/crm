<?php

namespace App\Http\Middleware;

use Carbon\CarbonInterval;
use Closure;
use Illuminate\Http\Request;

class SetLocale
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
        app()->setLocale(config('app.locale'));
        CarbonInterval::setLocale(config('app.locale'));

        if (auth()->check()) {
            app()->setLocale(auth()->user()->locale);
            CarbonInterval::setLocale(auth()->user()->locale);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class E2ETesting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        // if ($request->hasHeader('X-Playwright')) {
        //     abort(Response::HTTP_FORBIDDEN);
        // }
        if (app()->environment('production')) {
            abort(Response::HTTP_FORBIDDEN);
        }
        if (env('ALLOW_E2E_TESTING') !== true) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

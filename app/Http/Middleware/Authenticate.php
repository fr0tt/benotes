<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        /*
        if (!$request->expectsJson()) {
            return route('login');
        }
        */
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (in_array(/*'auth:sanctum'*/'api', $guards) && Auth::guard('api')->check()) {
            return $next($request);
        }

        if (in_array('share', $guards)) {
            if (Auth::guard('share')->check()) {
                config()->set('auth.defaults.guard', 'share');
                return $next($request);
            }
        }
        //$this->authenticate($request, $guards);
        return response('', Response::HTTP_UNAUTHORIZED);
    }
}

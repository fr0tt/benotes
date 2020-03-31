<?php

namespace App\Providers;

use App\Share;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {

        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            return app('auth')->setRequest($request)->user();
        });

        $this->app['auth']->viaRequest('token', function ($request) {
            if ($request->bearerToken()) { // $request->input('token')
                return Share::where('token', $request->bearerToken())->where('is_active', true)->first();
            }
        });
    }
}

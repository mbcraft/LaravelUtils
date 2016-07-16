<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 16/07/16
 * Time: 10.18
 */

namespace Mbcraft\Laravel\Providers;

use Sentinel;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\Access\Gate;
use Mbcraft\Laravel\Providers\Sentinel2BridgePolicyServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

class SentinelBridgePolicyServiceProvider extends AuthServiceProvider
{
    protected $policies = [];
    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     */
    public function boot(GateContract $gate)
    {
        foreach ($this->policies as $key => $value) {
            $gate->policy($key,$value);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAuthenticator();

        $this->registerUserResolver();

        $this->registerAccessGate();

        $this->registerRequestRebindHandler();
    }

    /**
     * Register the authenticator services.
     *
     */
    protected function registerAuthenticator()
    {
        $this->app->singleton('auth', function ($app) {
            // Once the authentication service has actually been requested by the developer
            // we will set a variable in the application indicating such. This helps us
            // know that we need to set any queued cookies in the after event later.
            $app['auth.loaded'] = true;

            return new AuthManager($app);
        });

        $this->app->singleton('auth.driver', function ($app) {
            return $app['auth']->driver();
        });
    }

    /**
     * Register a resolver for the authenticated user.
     *
     */
    protected function registerUserResolver()
    {
        $this->app->bind(AuthenticatableContract::class, function ($app) {
            return Sentinel::getUser();
        });
    }

    /**
     * Register the access gate service.
     *
     */
    protected function registerAccessGate()
    {
        $this->app->singleton(GateContract::class, function ($app) {
            return new Gate($app, function () use ($app) {
                return Sentinel::getUser();
            });
        });
    }

    /**
     * Register a resolver for the authenticated user.
     *
     */
    protected function registerRequestRebindHandler()
    {
        $this->app->rebinding('request', function ($app, $request) {
            $request->setUserResolver(function () use ($app) {
                return Sentinel::getUser();
            });
        });
    }
}
<?php

namespace Lujo\Lumen\Rest;

use Illuminate\Support\ServiceProvider;

class LumenRestServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('Lujo\Lumen\Rest\RestRoute', function () {
            return new RestRoute();
        });
    }
}
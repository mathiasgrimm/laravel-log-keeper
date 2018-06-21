<?php

namespace LifeOnScreen\LaravelLogKeeper\Providers;

use Exception;
use Illuminate\Support\ServiceProvider as Provider;

class LaravelServiceProvider extends Provider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Publishing the configuration file.
            $this->publishes([
                __DIR__ . '/../../config/laravel-log-keeper.php' => config_path('laravel-log-keeper.php'),
            ], 'laravel-log-keeper.config');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/laravel-log-keeper.php', 'laravel-log-keeper');

        $this->app->singleton('command.laravel-log-keeper', function ($app) {
            return $app['LifeOnScreen\LaravelLogKeeper\Commands\LogKeeper'];
        });

        $this->commands('command.laravel-log-keeper');
    }
}
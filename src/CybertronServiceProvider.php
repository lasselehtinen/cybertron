<?php

namespace lasselehtinen\Cybertron;

use Illuminate\Support\ServiceProvider;

class CybertronServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.cybertron.make.transformer', function ($app) {
            return $app['lasselehtinen\Cybertron\Commands\TransformerMakeCommand'];
        });

        $this->commands('command.cybertron.make.transformer');

        $this->loadViewsFrom(__DIR__ . '/Views', 'cybertron');
    }
}

<?php

namespace Luminark\Rivet;

use Illuminate\Support\ServiceProvider;

class RivetServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'migrations');
    }
    
    public function register()
    {
        $this->app->register(RivetEventServiceProvider::class);
    }
}

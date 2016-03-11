<?php

namespace Luminark\Rivet;

use Illuminate\Support\ServiceProvider;
use Luminark\Rivet\Interfaces\FileProcessorInterface;
use Luminark\Rivet\Interfaces\AttachingListenerInterface;
use Luminark\Rivet\Services\FileProcessor;
use Luminark\Rivet\Listeners\AttachingListener;

class RivetServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'migrations');
        $this->publishes([
            __DIR__ . '/../config/luminark/rivet.php' => config_path('luminark/courier.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/luminark/rivet.php', 'luminark.rivet'
        );

        $this->app->bind(FileProcessorInterface::class, function ($app) {
            return $app->make(FileProcessor::class);
        });
        $this->app->bind(AttachingListenerInterface::class, function ($app) {
            return $app->make(AttachingListener::class);
        });
        $this->app->register(RivetEventServiceProvider::class);
    }
}

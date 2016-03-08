<?php

namespace Luminark\Rivet;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Luminark\Rivet\Events\AttachingToModel;
use Luminark\Rivet\Listeners\AttachingListener;

class RivetEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AttachingToModel::class => [
            AttachingListener::class
        ]
    ];
}

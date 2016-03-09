<?php

namespace Luminark\Rivet;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Luminark\Rivet\Events\AttachingToModel;
use Luminark\Rivet\Interfaces\AttachingListenerInterface;

class RivetEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        /*AttachingToModel::class => [
            AttachingListenerInterface::class
        ]*/
    ];
}

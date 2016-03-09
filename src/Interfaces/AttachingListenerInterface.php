<?php

namespace Luminark\Rivet\Interfaces;

use Luminark\Rivet\Events\AttachingToModel;

interface AttachingListenerInterface
{
    public function handle(AttachingToModel $event);
}
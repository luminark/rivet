<?php

namespace Luminark\Rivet\Events;

use Illuminate\Database\Eloquent\Model;
use Luminark\Rivet\Models\Rivet;

abstract class AbstractAttachingEvent
{
    public $model;

    public $collection;

    public $rivet;

    public function __construct(Model $model, $collection, Rivet $rivet)
    {
        $this->model = $model;
        $this->collection = $collection;
        $this->rivet = $rivet;
    }
}

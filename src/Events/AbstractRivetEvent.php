<?php

namespace Luminark\Rivet\Events;

use Illuminate\Database\Eloquent\Model;
use Luminark\Rivet\Models\Rivet;

abstract class AbstractRivetEvent
{
    public $model;
    
    public $name;
    
    public $rivet;
    
    public $data;
    
    public function __construct(Model $model, $name, Rivet $rivet, array $data)
    {
        $this->model = $model;
        $this->name = $name;
        $this->rivet = $rivet;
        $this->data = $data;
    }
}

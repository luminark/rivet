<?php

namespace Luminark\Rivet\Events;

use Illuminate\Database\Eloquent\Model;
use Luminark\Rivet\Models\Rivet;
use Symfony\Component\HttpFoundation\File\File;

abstract class AbstractRivetFileEvent
{
    public $model;
    
    public $rivet;
    
    public $file;
    
    public function __construct(Model $model, Rivet $rivet, File $file)
    {
        $this->model = $model;
        $this->rivet = $rivet;
        $this->file = $file;
    }
}

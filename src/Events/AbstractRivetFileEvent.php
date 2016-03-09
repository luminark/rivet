<?php

namespace Luminark\Rivet\Events;

use Illuminate\Database\Eloquent\Model;
use Luminark\Rivet\Models\Rivet;
use Symfony\Component\HttpFoundation\File\File;

abstract class AbstractRivetFileEvent
{
    public $rivet;
    
    public $file;
    
    public function __construct(Rivet $rivet, File $file)
    {
        $this->rivet = $rivet;
        $this->file = $file;
    }
}

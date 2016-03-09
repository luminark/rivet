<?php

namespace Luminark\Rivet\Events;

use Luminark\Rivet\Models\Rivet;

class MovedRivetFile
{
    public $rivet;
    
    public $filePath;
    
    public function __construct(Rivet $rivet, $filePath)
    {
        $this->rivet = $rivet;
        $this->filePath = $filePath;
    }
}

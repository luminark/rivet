<?php

namespace Luminark\Rivet\Interfaces;

use Luminark\Rivet\Models\Rivet;
use Illuminate\Database\Eloquent\Model;

interface FileProcessorInterface
{
    public function processFile(Rivet $rivet, $file);
}

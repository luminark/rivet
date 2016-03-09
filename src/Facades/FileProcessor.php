<?php 

namespace Luminark\Rivet\Facades;

use Illuminate\Support\Facades\Facade;
use Luminark\Rivet\Interfaces\FileProcessorInterface;

class FileProcessor extends Facade
{
    protected static function getFacadeAccessor() 
    { 
        return FileProcessorInterface::class; 
    }
}
<?php

namespace Luminark\Rivet\Test;

use Luminark\Rivet\Models\Rivet;

class Attachment extends Rivet
{
    protected $fillable = ['title', 'size'];

    public static function getMorphToManyName()
    {
        return 'attachable';
    }
}
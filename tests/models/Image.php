<?php

namespace Luminark\Rivet\Test;

use Illuminate\Database\Eloquent\Model;
use Luminark\Rivet\Models\Rivet;
use Luminark\Rivet\Traits\UsesRivetsTableTrait;

class Image extends Rivet
{
    use UsesRivetsTableTrait;
    
    protected function getSerializableAttributes()
    {
        return ['file', 'alt', 'title'];
    }
}

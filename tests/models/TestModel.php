<?php

namespace Luminark\Rivet\Test;

use Illuminate\Database\Eloquent\Model;
use Luminark\Rivet\Models\Rivet;
use Luminark\Rivet\Traits\HasRivetsTrait;

/**
 * @method Rivet addRivet($data, $relationShouldLoad = true)
 * @method TestModel removeRivet($data, $relationShouldLoad = true)
 * @method Image setImage($image, $relationShouldLoad = true)
 * @method TestModel unsetImage($image, $relationShouldLoad = true)
 * @property Collection attachments
 * @property Image image
 */
class TestModel extends Model
{
    use HasRivetsTrait;
    
    protected function getRivetsConfig()
    {
        return [
            'samples' => ['collection', Rivet::class],
            'image' => ['property', Image::class],
            'attachments' => ['collection', Attachment::class]
        ];
    }
}

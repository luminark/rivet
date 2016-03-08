<?php

namespace Luminark\Rivet\Models;

use Illuminate\Database\Eloquent\Model;
use Luminark\SerializableValues\Traits\HasSerializableValuesTrait;
use Illuminate\Support\Str;

/**
 * @property string storage_path
 * @property string type
 * @property array values
 * @property stdClass file
 */
class Rivet extends Model
{
    use HasSerializableValuesTrait;
    
    protected $fillable = ['type', 'values'];
    
    protected function getSerializableAttributes()
    {
        return ['file'];
    }
    
    /** @todo remove */
    public function getStoragePathAttribute()
    {
        $dir = str_replace('\\', '', str_replace('_', '-', Str::snake(class_basename($this))));
        $sub = str_replace('_', '-', Str::snake($this->type));
        
        return $dir . DIRECTORY_SEPARATOR . $sub;
    }
    
    public static function getMorphToSortableManyOtherKey()
    {
        return null;
    }
}

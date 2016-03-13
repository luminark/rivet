<?php

namespace Luminark\Rivet\Models;

use Illuminate\Database\Eloquent\Model;
use Luminark\SerializableValues\Traits\HasSerializableValuesTrait;
use Luminark\Rivet\Models\Scopes\RivetTypeScope;
use Illuminate\Support\Str;

/**
 * @property string type
 * @property array values
 * @property stdClass file
 */
class Rivet extends Model
{
    use HasSerializableValuesTrait;

    public static function boot()
    {
        parent::boot();

        if (static::class == Rivet::class) {
            static::addGlobalScope(new RivetTypeScope(static::class));
            static::saving(function ($model) {
                $model->type = static::class;
            });
        }
    }

    protected $fillable = ['values'];

    protected function getSerializableAttributes()
    {
        return ['file'];
    }

    public static function getMorphToManyName()
    {
        return 'rivetable';
    }

    public static function getMorphToSortableManyOtherKey()
    {
        return null;
    }
}

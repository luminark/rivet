<?php 

namespace Luminark\Rivet\Traits;

use Luminark\Rivet\Models\Scopes\RivetTypeScope;

trait UsesRivetsTableTrait
{
    public static function boot()
    {
        parent::boot();
        
        static::addGlobalScope(new RivetTypeScope(static::getType()));
        static::saving(function ($model) {
            $model->type = $model::getType();
        });
    }
    
    public function getTable()
    {
        return 'rivets';
    }
    
    public static function getMorphToSortableManyOtherKey()
    {
        return 'rivet_id';
    }
    
    protected static function getType()
    {
        return static::class;
    }
}
<?php 

namespace Luminark\Rivet\Traits;

trait UsesRivetsTableTrait
{
    public function getTable()
    {
        return 'rivets';
    }
    
    public static function getMorphToSortableManyOtherKey()
    {
        return 'rivet_id';
    }
}
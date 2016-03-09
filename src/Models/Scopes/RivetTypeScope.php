<?php

namespace Luminark\Rivet\Models\Scopes;

use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RivetTypeScope implements ScopeInterface
{
    protected $type;
    
    public function __construct($type)
    {
        $this->type = $type;
    }
    
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('type', $this->type);
    }
    
    public function remove(Builder $builder, Model $model)
    {
        $query = $builder->getQuery();
        
        foreach((array) $query->wheres as $key => $where) {
            if ($key == 'type') {
                unset($query->wheres[$key]);
            }
        }
        
        $query->wheres = array_values($query->wheres);
    }
}
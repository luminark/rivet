<?php

namespace Luminark\Rivet\Traits;

use Luminark\Rivet\Models\Rivet;
use Luminark\Rivet\Events\AttachingToModel;
use Luminark\Rivet\Events\AttachedToModel;
use Rutorika\Sortable\MorphToSortedManyTrait;
use InvalidArgumentException;

trait HasRivetsTrait
{
    use MorphToSortedManyTrait;
    
    protected function getRivetsConfig()
    {
        return $this->rivets ?: ['rivets' => []];
    }
    
    protected function getRivetClass()
    {
        return Rivet::class;
    }
    
    public function attach($attribute, $rivet, $relationShouldLoad = false)
    {
        list($type, $class) = $this->getRivetConfig($attribute);
        
        if (is_numeric($rivet)) {
            $rivet = $class::findOrFail($rivet);
        } elseif ( ! $rivet instanceof $class) {
            throw new InvalidArgumentException("Invalid object provided for attaching to $attribute. Expected $class, got " . get_class($rivet) . ".");
        }
        
        if ($type == 'property') {
            $this->attachAsProperty($attribute, $rivet);
        } elseif ($type == 'collection') {
            $this->attachToCollection($attribute, $rivet);
        } else {
            throw new InvalidArgumentException("Invalid type [$type] provided for attaching to $attribute.");
        }
        
        if($relationShouldLoad) {
            $this->load($attribute);
        }
    }
    
    protected function attachAsProperty($attribute, Rivet $rivet)
    {
        if ($this->$attribute) {
            $this->removeRivet($attribute, $this->$attribute->id);
        }
        
        return $this->attachToCollection($attribute, $rivet);
    }
    
    protected function attachToCollection($collection, Rivet $rivet)
    {
        $dispatcher = static::getEventDispatcher();
        $dispatcher->fire(new AttachingToModel($this, $collection, $rivet));
        
        $rivet->save();
        $this->$collection()->attach($rivet, ['collection' => $collection]);
        
        $dispatcher->fire(new AttachedToModel($this, $collection, $rivet));
        
        return $rivet;
    }
    
    public function removeRivet($attribute, $rivet, $relationShouldLoad = false)
    {
        list(, $class) = $this->getRivetConfig($attribute);
        
        $result = null;
        if (is_numeric($rivet)) {
            $result = $this->$attribute()->detach($rivet);
        } elseif ($rivet instanceof $class) {
            $result = $this->$attribute()->detach($rivet->id);
        } else {
            throw new InvalidArgumentException('Only an ID or a Rivet object can be passed to removeRivet method.');
        }
        
        if ( ! $result) {
            throw new InvalidArgumentException('Unrelated ID or Rivet object passed to removeRivet method.');
        }
        
        if($relationShouldLoad) {
            $this->load($attribute);
        }
        
        return $this;
    }
    
    public function __call($name, $arguments)
    {
        if (preg_match('/^set(\w+)/', $name, $matches)
            && in_array($property = lcfirst($matches[1]), array_keys($this->getRivetsConfig()))) {
            array_unshift($arguments, $property);
            return call_user_func_array([$this, 'attach'], $arguments);
        }
        
        if (preg_match('/^add(\w+)/', $name, $matches)
            && in_array($collection = str_plural(lcfirst($matches[1])), array_keys($this->getRivetsConfig()))) {
            array_unshift($arguments, $collection);
            return call_user_func_array([$this, 'attach'], $arguments);
        }
        
        if (preg_match('/^unset(\w+)/', $name, $matches)
            && in_array($property = lcfirst($matches[1]), array_keys($this->getRivetsConfig()))) {
            array_unshift($arguments, $property);
            return call_user_func_array([$this, 'removeRivet'], $arguments);
        }
        
        if (preg_match('/^remove(\w+)/', $name, $matches)
            && in_array($collection = str_plural(lcfirst($matches[1])), array_keys($this->getRivetsConfig()))) {
            array_unshift($arguments, $collection);
            return call_user_func_array([$this, 'removeRivet'], $arguments);
        }
        
        if ( ! in_array($name, array_keys($this->getRivetsConfig()))) {
            return parent::__call($name, $arguments);
        }
        
        list($type, $class) = $this->getRivetConfig($name);
        
        if ($type == 'property') {
            return $this->getRivetProperty($class, $name);
        }
        if ($type == 'collection') {
            return $this->getRivetCollection($class, $name);
        }
    }
    
    public function __get($name)
    {
        $result = parent::__get($name);
        
        if ( ! in_array($name, array_keys($this->getRivetsConfig()))) {
            return $result;
        }
        
        if ( ! $result) {
            $this->load($name);
            $result = $this->getAttribute($name);
        }
        
        list($type) = $this->getRivetConfig($name);
        if ($type == 'property') {
            return $result->first();
        }
        
        return $result;
    }
    
    protected function getRivetConfig($name)
    {
        return $this->getRivetsConfig()[$name]
            + ['collection', $this->getRivetClass()];
    }
    
    protected function getRivetProperty($class, $name)
    {
        return $this->getRivetCollection($class, $name);
    }
    
    protected function getRivetCollection($class, $name)
    {
        return $this->morphToSortedMany(
            $class, 
            'rivetable',
            'position', 
            null, 
            null,
            $class::getMorphToSortableManyOtherKey()
        )->wherePivot('collection', snake_case($name));
    }
    
    abstract public function getAttribute($key);
    
    abstract public function load($relations);
}

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
    
    public function attach($name, $data, $relationShouldLoad = true)
    {
        list($type, $class) = $this->getRivetConfig($name);
        
        $rivet = null;
        if ($type == 'property') {
            $rivet = $this->attachAsProperty($name, $class, $data);
        }
        if ($type == 'collection') {
            $rivet = $this->attachToCollection($name, $class, $data);
        }
        
        if($relationShouldLoad) {
            $this->load($name);
        }
        
        return $rivet;
    }
    
    protected function attachAsProperty($name, $class, array $data)
    {
        if ($this->$name) {
            $this->removeRivet($name, $this->$name->id);
        }
        
        return $this->attachToCollection($name, $class, $data);
    }
    
    protected function attachToCollection($name, $class, array $data)
    {
        $data['type'] = $name;
        $rivet = new $class(array_except($data, ['file']));
        
        event(new AttachingToModel($this, $name, $rivet, $data));
        
        $rivet->save();
        
        $this->$name()->attach($rivet, ['collection' => $name]);
        
        event(new AttachedToModel($this, $name, $rivet, $data));
        
        return $rivet;
    }
    
    public function removeRivet($name, $rivet, $relationShouldLoad = true)
    {
        list(, $class) = $this->getRivetConfig($name);
        
        $result = null;
        if (is_numeric($rivet)) {
            $result = $this->$name()->detach($rivet);
        } elseif ($rivet instanceof $class) {
            $result = $this->$name()->detach($rivet->id);
        } else {
            throw new InvalidArgumentException('Only an ID or a Rivet object can be passed to removeRivet method.');
        }
        
        if ( ! $result) {
            throw new InvalidArgumentException('Unrelated ID or Rivet object passed to removeRivet method.');
        }
        
        if($relationShouldLoad) {
            $this->load($name);
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
        
        list($type) = $this->getRivetConfig($name);
        
        if ($type == 'property' && $result) {
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
        )->where('type', snake_case($name));
    }
    
    abstract public function load($relations); 
}

<?php

namespace Luminark\Rivet\Traits;

use Luminark\Rivet\Models\Rivet;
use Luminark\Rivet\Facades\FileProcessor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

trait ControlsRivetsTrait
{
    protected function createRivet($class, $data)
    {
        $fileAttributes = $this->getFileAttributes($class);
        $rivet = new $class(array_except($data, $fileAttributes));
        $fileProcessor = $this->getFileProcessor();
        foreach ($fileAttributes as $attribute) {
            if (array_has($data, $attribute)) {
                $rivet->$attribute = $fileProcessor->processFile($rivet, $data[$attribute]);
            }
        }

        $rivet->save();

        return $rivet;
    }

    protected function updateRivet(Rivet $rivet, $data)
    {
        $fileAttributes = $this->getFileAttributes(get_class($rivet));
        $fileProcessor = $this->getFileProcessor();
        $oldFiles = [];
        foreach ($fileAttributes as $attribute) {
            if (array_has($data, $attribute)) {
                $oldFiles[] = $this->getFilePath($rivet, $attribute);
                $rivet->$attribute = $fileProcessor->processFile($rivet, $data[$attribute]);
            }
        }
        $rivet->fill(array_except($data, $fileAttributes));

        $rivet->save();

        $this->getStorage()->delete($oldFiles);
    }

    protected function getFileAttributes($class)
    {
        return $this->getConfig()->get(
            'luminark.rivet.' . $class . '.file_attributes', []
        );
    }

    protected function getConfig()
    {
        return Config::getFacadeRoot();
    }

    protected function getFileProcessor()
    {
        return FileProcessor::getFacadeRoot();
    }

    protected function getStorage()
    {
        return Storage::getFacadeRoot();
    }

    protected function getFilePath(Rivet $rivet, $attribute)
    {
        return object_get($rivet->$attribute, 'path');
    }
}

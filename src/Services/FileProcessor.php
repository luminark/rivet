<?php

namespace Luminark\Rivet\Services;

use Luminark\Rivet\Interfaces\FileProcessorInterface;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Config\Repository as Config;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Luminark\Rivet\Models\Rivet;
use Luminark\Rivet\Events\CopyingRivetFile;
use Luminark\Rivet\Events\CopiedRivetFile;
use Luminark\Rivet\Events\MovingRivetFile;
use Luminark\Rivet\Events\MovedRivetFile;

class FileProcessor implements FileProcessorInterface
{
    protected $storage;
    
    protected $dispatcher;
    
    protected $config;
    
    public function __construct(
        Storage $storage, 
        Dispatcher $dispatcher, 
        Config $config
    ) {
        $this->storage = $storage;
        $this->dispatcher = $dispatcher;
        $this->config = $config;
    }
    
    public function processFile(Rivet $rivet, $file)
    {
        if (is_string($file)) {
            $file = new File($file);
        }
        
        $this->dispatcher->fire(
            new CopyingRivetFile($rivet, $file)
        );

        $tempFile = $this->copyFile($file);
        
        $this->dispatcher->fire(
            new CopiedRivetFile($rivet, $tempFile)
        );
        
        $filePath = $this->getStoragePath(get_class($rivet)) 
            . DIRECTORY_SEPARATOR 
            . $tempFile->getBasename();
        while($this->storage->exists($filePath)) {
            $filePath = $this->generateUniqueFilename($filePath);
        }
        
        $this->dispatcher->fire(
            new MovingRivetFile($rivet, $tempFile)
        );
        
        $this->storage->put($filePath, file_get_contents($tempFile->getRealPath()));
        
        $this->dispatcher->fire(
            new MovedRivetFile($rivet, $filePath)
        );
        
        $fileInfo = $this->getFileInfo($filePath, $tempFile);
        unlink($tempFile->getRealPath());
        
        return $fileInfo;
    }
    
    protected function copyFile(File $file)
    {
        if ($file instanceof UploadedFile) {
            $filename = $file->getClientOriginalName();
        } else {
            $filename = $file->getFilename();
        }
        
        $tempStoragePath = $this->getStoragePath('temp');
        $tempPath = $tempStoragePath . DIRECTORY_SEPARATOR . $filename;
        
        if ( ! file_exists($tempStoragePath)) {
            mkdir($tempStoragePath, 0775, true);
        }
        
        copy($file->getRealPath(), $tempPath);
        
        return new File($tempPath);
    }
    
    protected function getStoragePath($class)
    {
        return $this->config->get(
            'luminark.rivet.storage.' . $class,
            $this->config->get('luminark.rivet.storage.default')
        );
    }
    
    protected function generateUniqueFilename($string)
    {
        $string = explode('.', $string);
        $lastIndex = count($string) - 1;
        $string[$lastIndex] = str_random(5) . '.' . $string[$lastIndex];
        
        return implode('.', $string);
    }
    
    protected function getFileInfo($filePath, File $file)
    {
        return (object) [
            'path' => $filePath,
            'name' => basename($filePath),
            'mime' => $file->getMimeType(),
            'size' => $this->storage->size($filePath)
        ];
    }
}
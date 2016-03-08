<?php

namespace Luminark\Rivet\Listeners;

use Luminark\Rivet\Events\AttachingToModel;
use Luminark\Rivet\Events\MovedRivetFile;
use Luminark\Rivet\Events\MovingRivetFile;
use Illuminate\Contracts\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Luminark\Rivet\Models\Rivet;

class AttachingListener
{
    protected $filesystem;
    
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }
    
    public function handle(AttachingToModel $event)
    {
        if ( ! array_key_exists('file', $event->data)) {
            return;
        }
        
        //event(new MovingRivetFile($event->model, $event->rivet, $event->data['file']));

        $file = $this->copyFile($event->data['file']);
        
        event(new MovedRivetFile($event->model, $event->rivet, $file));
        
        //TODO get storage path from config
        $filePath = $event->rivet->storage_path . DIRECTORY_SEPARATOR . $file->getBasename();
        while($this->filesystem->exists($filePath)) {
            $filePath = $this->generateUniqueFilename($filePath);
        }
        
        $this->filesystem->put($filePath, file_get_contents($file->getRealPath()));
        
        $event->rivet->file = $this->getFileInfo($filePath, $file);
        
        unlink($file->getRealPath());
    }
    
    protected function copyFile($file)
    {
        if ($file instanceof UploadedFile) {
            $tempPath = $this->getTempDirectory($file->getClientOriginalName());
            if (!file_exists($this->getTempDirectory())) {
                mkdir($this->getTempDirectory(), 0777, true);
            }
            copy($file->getRealPath(), $tempPath);
            $file = new File($tempPath);
        } elseif ($file instanceof File) {
            // todo
        } else {
            // todo
        }
        
        return $file;
    }
    
    protected function getTempDirectory($path = '')
    {
        return storage_path('temp' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
    
    protected function getFileInfo($filePath, $file)
    {
        return (object) [
            'path' => $filePath,
            'name' => basename($filePath),
            'mime' => $file->getMimeType(),
            'size' => $this->filesystem->size($filePath)
        ];
    }
    
    protected function generateUniqueFilename($string)
    {
        $string = explode('.', $string);
        $lastIndex = count($string) - 1;
        $string[$lastIndex] = str_random(5) . '.' . $string[$lastIndex];
        
        return implode('.', $string);
    }
}

<?php

namespace Luminark\Rivet\Listeners;

use Luminark\Rivet\Events\AttachingToModel;
use Luminark\Rivet\Interfaces\FileProcessorInterface;
use Luminark\Rivet\Interfaces\AttachingListenerInterface;

class AttachingListener implements AttachingListenerInterface
{
    protected $fileProcessor;
    
    public function __construct(FileProcessorInterface $fileProcessor)
    {
        $this->fileProcessor = $fileProcessor;
    }
    
    public function handle(AttachingToModel $event)
    {
        if ( ! array_key_exists('file', $event->data)) {
            return null;
        }
        
        $fileInfo = $this->fileProcessor->processFile(
            $event->rivet, $event->data['file']
        );
        $event->rivet->file = $fileInfo;
    }
}

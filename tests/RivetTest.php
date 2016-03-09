<?php

namespace Luminark\Rivet\Test;

use Orchestra\Testbench\TestCase;
use Luminark\Url\Models\Url;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

use Luminark\Rivet\Models\Rivet;
use Luminark\Rivet\Interfaces\FileProcessorInterface;

/**
 * Class RivetTest
 */
class RivetTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
        copy(
            __DIR__ . '/../database/migrations/2015_04_12_000000_create_rivets_table.php',
            __DIR__ . '/../tests/database/migrations/2015_04_12_000000_create_rivets_table.php'
        );
        copy(
            __DIR__ . '/../database/migrations/2015_04_12_000001_create_rivetables_table.php',
            __DIR__ . '/../tests/database/migrations/2015_04_12_000001_create_rivetables_table.php'
        );
        copy(
            __DIR__ . '/files/sources/image.jpg',
            __DIR__ . '/files/image.jpg'
        );
        $this->artisan('migrate', [
          '--database' => 'testbench',
          '--path' => '../tests/database/migrations',
        ]);
        
        // Workaround to get Eloquent Model events working in tests
        TestModel::flushEventListeners();
        TestModel::boot();
    }
    
    public function tearDown()
    {
        $this->artisan('migrate:rollback', [
          '--database' => 'testbench'
        ]);
        
        $storageDir = $this->app['config']['filesystems.disks.local.root'];
        if ($storageDir) {
            foreach(glob($storageDir . DIRECTORY_SEPARATOR . '*') as $path) {
                if (is_dir($path)) {
                    foreach(glob($path . DIRECTORY_SEPARATOR . '*') as $file) {
                        unlink($file);
                    }
                }
            }
        }
    }
    
    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['path.base'] = __DIR__ . '/../src';
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
          'driver' => 'sqlite',
          'database' => ':memory:',
          'prefix' => '',
        ]);
    }
    
    /**
     * Get Luminark Rivet package providers.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Luminark\Rivet\RivetServiceProvider'];
    }
    
    public function testAttachingToCollection()
    {
        $model = TestModel::create([]);
        $uploadedFile = $this->getTestUploadedFile();
        $fileProcessor = $this->app->make(FileProcessorInterface::class);
        
        $rivet1 = new Rivet([]);
        $rivet1->file = $fileProcessor->processFile($rivet1, $uploadedFile);
        $rivet1->save();
        
        $model->attach('attachments', $rivet1, false);
        $model->addAttachment($rivet1, false);
        
        $model->load('attachments');
        
        $this->assertRegexp(
            '/image(\.\w+)?\.jpg/', 
            $model->attachments->get(0)->file->name, 
            'Invalid attachment filename.'
        );
        $this->assertRegexp(
            '/image(\.\w+)?\.jpg/', 
            $model->attachments->get(1)->file->name, 
            'Invalid attachment filename.'
        );
        $this->assertEquals(1, $model->attachments->get(0)->pivot->position, 'Invalid attachment position.');
        $this->assertEquals(2, $model->attachments->get(1)->pivot->position, 'Invalid attachment position.');
    }
    
    public function testAttachingAsProperty()
    {
        $model = TestModel::create([]);
        $uploadedFile = $this->getTestUploadedFile();
        $fileProcessor = $this->app->make(FileProcessorInterface::class);
        
        $image = new Image([
            'alt' => 'Image alt',
            'title' => 'Image Title'
        ]);
        $image->file = $fileProcessor->processFile($image, $uploadedFile);
        $image->save();
        
        $model->setImage($image, true);
        
        $this->assertEquals('Image alt', $model->image->alt);
        $this->assertEquals('Image Title', $model->image->title);
        
        $this->assertRegexp(
            '/image(\.\w+)?\.jpg/', 
            $model->image->file->name, 
            'Invalid image filename.'
        );
        
        $image = new Image([
            'alt' => 'Image alt 2',
            'title' => 'Image Title 2'
        ]);
        $image->file = $fileProcessor->processFile($image, $uploadedFile);
        $image->save();
        
        $model->setImage($image, true);
        
        $this->assertRegexp(
            '/image(\.\w+)?\.jpg/', 
            $model->image->file->name, 
            'Invalid image filename.'
        );
        $this->assertEquals('Image alt 2', $model->image->alt);
        $this->assertEquals('Image Title 2', $model->image->title);
    }
    
    public function testAttachingWithoutFile()
    {
        $model = TestModel::create([]);
        $model->addAttachment(Rivet::create([]), true);
        
        $this->assertEquals(1, $model->attachments->count(), 'Invalid number of attachments in collection.');
    }
    
    public function testRemovingAttachments()
    {
        $model = TestModel::create([]);
        $uploadedFile = $this->getTestUploadedFile();
        $fileProcessor = $this->app->make(FileProcessorInterface::class);
        
        $attachment1 = new Rivet([]);
        $attachment2 = new Rivet([]);
        $attachment1->file = $fileProcessor->processFile($attachment1, $uploadedFile);
        $attachment2->file = $fileProcessor->processFile($attachment2, $uploadedFile);
        $attachment1->save();
        $attachment2->save();
        
        $model->addAttachment($attachment1);
        $model->addAttachment($attachment2);
        
        $image = new Image([
            'alt' => 'Image alt',
            'title' => 'Image Title'
        ]);
        $image->file = $fileProcessor->processFile($image, $uploadedFile);
        $image->save();
        
        $model->setImage($image);
        
        $model->removeAttachment($attachment1);
        $model->removeAttachment($attachment2->id);
        $model->unsetImage($image);
        
        $this->assertEquals(0, $model->attachments->count(), 'Collection attachment objects not properly removed.');
        $this->assertEquals(null, $model->image, 'Property attachment object not properly removed.');
    }
    
    public function testRemovingNonExistantAttachment()
    {
        $model = TestModel::create([]);
        $attachment = Rivet::create([]);
        $model->addAttachment($attachment);
        
        $this->setExpectedException('InvalidArgumentException');
        
        $model->removeAttachment($attachment->id + 1);
    }
    
    public function testRemovingWithInvalidParameter()
    {
        $model = TestModel::create([]);
        
        $this->setExpectedException('InvalidArgumentException');
        
        $model->removeAttachment('foo');
    }
    
    public function testRivetController()
    {
        $this->app['config']->set(
            'luminark.rivet.' . Image::class . '.file_attributes',
            ['file']
        );
        $mockRequest = \Mockery::mock('Illuminate\Http\Request');
        $mockRequest->shouldReceive('all')
            ->andReturn([
                'title' => 'Title',
                'alt' => 'Alt text',
                'file' => $this->getTestUploadedFile()
            ]);
        
        $controller = new TestController();
        $image = $controller->create($mockRequest);
        
        $this->assertEquals('Title', $image->title, 'Invalid title set on image.');
        $this->assertEquals('Alt text', $image->alt, 'Invalid alt text set on image.');
        $this->assertRegexp(
            '/image(\.\w+)?\.jpg/', 
            $image->file->name, 
            'Invalid image filename.'
        );
        
        $imagePath = $image->file->path;
        
        $mockRequest = \Mockery::mock('Illuminate\Http\Request');
        $mockRequest->shouldReceive('all')
            ->andReturn([
                'title' => 'Title 2',
                'alt' => 'Alt text 2'
            ]);
            
        $controller->update($image->id, $mockRequest);
        
        $image = Image::findOrFail($image->id);
            
        $this->assertEquals('Title 2', $image->title, 'Invalid title set on image.');
        $this->assertEquals('Alt text 2', $image->alt, 'Invalid alt text set on image.');
        $this->assertEquals(
            $imagePath, 
            $image->file->path, 
            'Invalid image path after update.'
        );
        
        $mockRequest = \Mockery::mock('Illuminate\Http\Request');
        $mockRequest->shouldReceive('all')
            ->andReturn([
                'file' => $this->getTestUploadedFile()
            ]);
            
        $controller->update($image->id, $mockRequest);
        
        $image = Image::findOrFail($image->id);
        $storage = $this->app['filesystem'];
        
        $this->assertNotEquals($imagePath, $image->file->path, 'File path not updated properly.');
        $this->assertFalse($storage->exists($imagePath), 'Old file not deleted.');
    }
    
    public function testDeletingAttachment()
    {
        $model = TestModel::create([]);
        $uploadedFile = $this->getTestUploadedFile();
        $fileProcessor = $this->app->make(FileProcessorInterface::class);
        
        $rivet1 = new Rivet([]);
        $rivet1->file = $fileProcessor->processFile($rivet1, $uploadedFile);
        $rivet1->save();
        
        $model->addAttachment($rivet1);
    }
    
    public function testAttachmentFiles()
    {
    }
    
    protected function getTestUploadedFile()
    {
        return new UploadedFile(
            $this->getTestFilePath(), 
            'image.jpg', 
            null, null, null, true
        );
    }
    
    protected function getTestFilePath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'image.jpg';
    }
}

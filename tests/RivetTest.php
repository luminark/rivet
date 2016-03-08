<?php

namespace Luminark\Rivet\Test;

use Orchestra\Testbench\TestCase;
use Luminark\Url\Models\Url;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

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
     * Get Luminark Url package providers.
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
        
        $rivet1 = $model->attach('attachments', ['file' => $uploadedFile], false);
        $rivet2 = $model->addAttachment(['file' => $uploadedFile], false);
        
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
        
        $image = $model->setImage([
            'file' => $uploadedFile,
            'alt' => 'Image alt',
            'title' => 'Image Title'
        ]);
        
        $this->assertEquals('Image alt', $model->image->alt);
        $this->assertEquals('Image Title', $model->image->title);
        
        $this->assertRegexp(
            '/image(\.\w+)?\.jpg/', 
            $model->image->file->name, 
            'Invalid image filename.'
        );
        
        $image = $model->setImage([
            'file' => $uploadedFile,
            'alt' => 'Image alt 2',
            'title' => 'Image Title 2'
        ]);
        
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
        $model->addAttachment([]);
        
        $this->assertEquals(1, $model->attachments->count(), 'Invalid number of attachments in collection.');
    }
    
    public function testRemovingAttachments()
    {
        $model = TestModel::create([]);
        $uploadedFile = $this->getTestUploadedFile();
        
        $attachment1 = $model->addAttachment(['file' => $uploadedFile]);
        $attachment2 = $model->addAttachment(['file' => $uploadedFile]);
        
        $image = $model->setImage([
            'file' => $uploadedFile,
            'alt' => 'Image alt',
            'title' => 'Image Title'
        ]);
        
        $model->removeAttachment($attachment1);
        $model->removeAttachment($attachment2->id);
        $model->unsetImage($image);
        
        $this->assertEquals(0, $model->attachments->count(), 'Collection attachment objects not properly removed.');
        $this->assertEquals(null, $model->image, 'Property attachment object not properly removed.');
    }
    
    public function testRemovingNonExistantAttachment()
    {
        $model = TestModel::create([]);
        $attachment = $model->addAttachment([]);
        
        $this->setExpectedException('InvalidArgumentException');
        
        $model->removeAttachment($attachment->id + 1);
    }
    
    public function testRemovingWithInvalidParameter()
    {
        $model = TestModel::create([]);
        
        $this->setExpectedException('InvalidArgumentException');
        
        $model->removeAttachment('foo');
    }
    
    public function testDeletingAttachment()
    {
        $model = TestModel::create([]);
        $uploadedFile = $this->getTestUploadedFile();
        
        $attachment1 = $model->addAttachment(['file' => $uploadedFile]);
    }
    
    public function testAttachmentFiles()
    {
        $model = TestModel::create([]);
        $file = new File($this->getTestFilePath());
        $attachment1 = $model->addAttachment(['file' => $file]);
        
        $model = TestModel::create([]);
        $attachment2 = $model->addAttachment(['file' => $this->getTestFilePath()]);
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

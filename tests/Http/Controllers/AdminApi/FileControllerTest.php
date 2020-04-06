<?php

namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\AdminApi;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\ModuleInstance\Settings\ModuleInstanceSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class FileControllerTest extends TestCase
{

    
    /** @test */
    public function it_returns_a_403_error_if_the_permission_is_not_owned(){
        $this->revokePermissionTo('uploadfile.admin.file.index');
        
        $response = $this->getJson($this->adminApiUrl('/file'));
        $response->assertStatus(403);
    }
    
    /** @test */
    public function it_returns_a_200_permission_if_the_permission_is_owned(){
        $this->givePermissionTo('uploadfile.admin.file.index');

        $response = $this->getJson($this->adminApiUrl('/file'));
        $response->assertStatus(200);
    }
    
    /** @test */
    public function it_returns_all_files_belonging_to_the_module_instance(){
        $this->bypassAuthorization();

        $files = factory(File::class, 5)->create([
            'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        $otherFiles = factory(File::class, 3)->create();

        $response = $this->getJson($this->adminApiUrl('/file'));
        $response->assertStatus(200);
        $response->assertJsonCount(5);
        foreach($files as $file) {
            $response->assertJsonFragment([
                'title' => $file->title,
                'description' => $file->description,
                'filename' => $file->filename
            ]);
        }
    }

    /** @test */
    public function show_returns_a_403_error_if_the_permission_is_not_owned(){
        $this->revokePermissionTo('uploadfile.admin.file.index');
        
        $file = factory(File::class)->create([
            'module_instance_id' => $this->getModuleInstance()->id(),
        ]);
        
        $response = $this->getJson($this->adminApiUrl('/file/' . $file->id));
        $response->assertStatus(403);
    }

    /** @test */
    public function show_returns_a_200_permission_if_the_permission_is_owned(){
        $this->givePermissionTo('uploadfile.admin.file.index');

        $file = factory(File::class)->create([
            'module_instance_id' => $this->getModuleInstance()->id(),
        ]);
        
        $response = $this->getJson($this->adminApiUrl('/file/' . $file->id));
        $response->assertStatus(200);
    }

    /** @test */
    public function show_returns_the_file(){
        $this->givePermissionTo('uploadfile.admin.file.index');

        $file = factory(File::class)->create([
            'module_instance_id' => $this->getModuleInstance()->id(),
        ]);

        $response = $this->getJson($this->adminApiUrl('/file/' . $file->id));
        $response->assertStatus(200);
        
        $response->assertJsonFragment([
            'id' => $file->id,
            'title' => $file->title
        ]);
    }


    /** @test */
    public function store_allows_a_single_file_to_be_uploaded()
    {
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => ['jpg', 'png'], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        $activityInstance = factory(ActivityInstance::class)->create();
        
        $this->bypassAuthorization();

        Storage::fake();
        $file = UploadedFile::fake()->create('filename.png', 58, 'image/png');

        $response = $this->postJson($this->adminApiUrl('/file'), [
            'file' => [$file],
            'title' => 'ATitle',
            'description' => 'ADescription',
            'activity_instance_id' => $activityInstance->id
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('uploadfile_files', [
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename.png',
            'mime' => 'image/png',
            'uploaded_by' => $this->getControlUser()->id(),
            'activity_instance_id' => $activityInstance->id,
            'module_instance_id' => $this->getModuleInstance()->id()
        ]);

        $file = File::where('title', 'ATitle')->get()->first();
        $this->assertInstanceOf(File::class, $file);

        Storage::assertExists($file->path);

    }

    /** @test */
    public function store_returns_the_file_meta_data_as_an_array()
    {
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => ['jpg', 'png'], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);

        $activityInstance = factory(ActivityInstance::class)->create();

        $this->bypassAuthorization();

        Storage::fake();
        $file = UploadedFile::fake()->create('filename.png', 58, 'image/png');

        $response = $this->postJson($this->adminApiUrl('/file'), [
            'file' => [$file],
            'title' => 'ATitle',
            'description' => 'ADescription',
            'activity_instance_id' => $activityInstance->id
        ]);
        $response->assertStatus(200);
        $response->assertJsonCount(1);

        $response->assertJsonFragment([
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename.png',
            'mime' => 'image/png',
            'activity_instance_id' => $activityInstance->id
        ]);
    }

    /** @test */
    public function store_allows_multiple_files_to_be_uploaded()
    {
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => ['jpg', 'png'], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        $activityInstance = factory(ActivityInstance::class)->create();

        $this->bypassAuthorization();

        Storage::fake();

        $file1 = UploadedFile::fake()->create('filename.png', 58, 'image/png');
        $file2 = UploadedFile::fake()->create('filename2.png', 58, 'image/png');

        $response = $this->postJson($this->adminApiUrl('/file'), [
            'file' => [$file1, $file2],
            'title' => 'ATitle',
            'description' => 'ADescription',
            'activity_instance_id' => $activityInstance->id
        ]);
        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename.png',
            'mime' => 'image/png',
            'activity_instance_id' => $activityInstance->id
        ]);
        $response->assertJsonFragment([
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename2.png',
            'mime' => 'image/png',
            'activity_instance_id' => $activityInstance->id
        ]);

        $this->assertDatabaseHas('uploadfile_files', [
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename.png',
            'mime' => 'image/png',
            'uploaded_by' => $this->getControlUser()->id(),
            'activity_instance_id' => $activityInstance->id,
            'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        $this->assertDatabaseHas('uploadfile_files', [
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename2.png',
            'mime' => 'image/png',
            'uploaded_by' => $this->getControlUser()->id(),
            'activity_instance_id' => $activityInstance->id,
            'module_instance_id' => $this->getModuleInstance()->id()
        ]);

        $files = File::where('title', 'ATitle')->get();
        $this->assertCount(2, $files);

        $this->assertInstanceOf(File::class, $files[0]);
        Storage::assertExists($files[0]->path);
        $this->assertInstanceOf(File::class, $files[1]);
        Storage::assertExists($files[1]->path);
    }

    /** @test */
    public function store_returns_a_200_if_the_permission_owned()
    {
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => ['jpg', 'png'], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        $activityInstance = factory(ActivityInstance::class)->create();

        $this->givePermissionTo('uploadfile.admin.file.store');

        Storage::fake();
        $file = UploadedFile::fake()->create('filename.png', 58, 'image/png');

        $response = $this->postJson($this->adminApiUrl('/file'), [
            'file' => [$file],
            'title' => 'ATitle',
            'description' => 'ADescription',
            'activity_instance_id' => $activityInstance->id
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function store_returns_a_403_if_the_permission_is_not_owned()
    {
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => ['jpg', 'png'], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        $activityInstance = factory(ActivityInstance::class)->create();

        $this->revokePermissionTo('uploadfile.admin.file.store');

        Storage::fake();
        $file = UploadedFile::fake()->create('filename.png', 58, 'image/png');

        $response = $this->postJson($this->adminApiUrl('/file'), [
            'file' => [$file],
            'title' => 'ATitle',
            'description' => 'ADescription',
            'activity_instance_id' => $activityInstance->id
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function store_fires_an_event_when_a_file_is_uploaded()
    {
        Event::fake(DocumentUploaded::class);
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => ['jpg', 'png'], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        $activityInstance = factory(ActivityInstance::class)->create();

        $this->bypassAuthorization();

        Storage::fake();
        $file = UploadedFile::fake()->create('filename.png', 58, 'image/png');

        $response = $this->postJson($this->adminApiUrl('/file'), [
            'file' => [$file],
            'title' => 'ATitle',
            'description' => 'ADescription',
            'activity_instance_id' => $activityInstance->id
        ]);
        $response->assertStatus(200);

        $fileMeta = File::where('title', 'ATitle')->get()->first();
        $this->assertInstanceOf(File::class, $fileMeta);

        Event::assertDispatched(DocumentUploaded::class, function($event) use ($fileMeta) {
            return $event instanceof DocumentUploaded && $event->file->is($fileMeta);
        });
    }

    /** @test */
    public function store_fires_an_event_for_each_file_uploaded()
    {
        Event::fake(DocumentUploaded::class);
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => ['jpg', 'png'], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        $activityInstance = factory(ActivityInstance::class)->create();

        $this->bypassAuthorization();

        Storage::fake();

        $file1 = UploadedFile::fake()->create('filename.png', 58, 'image/png');
        $file2 = UploadedFile::fake()->create('filename2.png', 58, 'image/png');

        $response = $this->postJson($this->adminApiUrl('/file'), [
            'file' => [$file1, $file2],
            'title' => 'ATitle',
            'description' => 'ADescription',
            'activity_instance_id' => $activityInstance->id
        ]);
        $response->assertStatus(200);

        $files = File::where('title', 'ATitle')->get();
        $this->assertCount(2, $files);

        Event::assertDispatched(DocumentUploaded::class, function($event) use ($files) {
            return $event instanceof DocumentUploaded && ($event->file->is($files[0]) || $event->file->is($files[1]));
        });
    }

    /** @test */
    public function store_returns_422_if_file_type_not_allowed(){
        Event::fake(DocumentUploaded::class);
        $activityInstance = factory(ActivityInstance::class)->create();
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => [], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);

        $this->bypassAuthorization();

        Storage::fake();

        $file1 = UploadedFile::fake()->create('filename.png', 58, 'image/png');
        $response = $this->postJson($this->adminApiUrl('/file'), [
            'file' => [$file1],
            'title' => 'ATitle',
            'description' => 'ADescription',
            'activity_instance_id' => $activityInstance->id
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['file.0' => 'file of type:']);

    }

}
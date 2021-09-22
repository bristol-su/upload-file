<?php

namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\ParticipantApi;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Events\DocumentDeleted;
use BristolSU\Module\UploadFile\Events\DocumentUpdated;
use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\ModuleInstance\Settings\ModuleInstanceSetting;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class FileControllerTest extends TestCase
{

    /** @test */
    public function index_returns_403_if_permission_not_given()
    {
        $this->revokePermissionTo('uploadfile.file.index');

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->getJson($this->userApiUrl('file'));
        $response->assertStatus(403);
    }

    /** @test */
    public function index_returns_200_if_permission_given()
    {
        $this->givePermissionTo('uploadfile.file.index');

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->getJson($this->userApiUrl('file'));
        $response->assertStatus(200);
    }

    /** @test */
    public function index_returns_all_files_for_the_resource()
    {
        $this->bypassAuthorization();

        $files = File::factory()->count(5)->create([
            'activity_instance_id' => $this->getActivityInstance()->id,
            'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        File::factory()->count(2)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        File::factory()->count(2)->create(['activity_instance_id' => $this->getActivityInstance()->id]);
        File::factory()->count(2)->create();

        $response = $this->getJson($this->userApiUrl('/file'));
        $response->assertStatus(200);

        $response->assertJsonCount(5);
        foreach ($files as $file) {
            $response->assertJsonFragment([
                'id' => $file->id,
                'filename' => $file->filename,
                'title' => $file->title,
                'description' => $file->description
            ]);
        }
    }

    /** @test */
    public function destroy_returns_403_if_permission_not_given()
    {
        $this->revokePermissionTo('uploadfile.file.destroy');

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->idc]);

        $response = $this->deleteJson($this->userApiUrl('file/' . $file->id));
        $response->assertStatus(403);
    }

    /** @test */
    public function destroy_returns_a_403_if_the_file_does_not_belong_to_the_activity_instance(){
        $this->bypassAuthorization();

        $activityInstance = ActivityInstance::factory()->create(['activity_id' => $this->getActivity()->id]);
        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $activityInstance->id]);

        $response = $this->deleteJson($this->userApiUrl('/file/' . $file->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function destroy_returns_200_if_permission_given()
    {
        $this->givePermissionTo('uploadfile.file.destroy');

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $response = $this->deleteJson($this->userApiUrl('file/' . $file->id));
        $response->assertStatus(200);
    }

    /** @test */
    public function destroy_returns_a_404_if_file_not_found()
    {
        $this->bypassAuthorization();
        $this->assertDatabaseMissing('uploadfile_files', ['id' => 100]);
        $response = $this->deleteJson($this->userApiUrl('file/100'));
        $response->assertStatus(404);
    }

    /** @test */
    public function destroy_deletes_a_file_from_the_database()
    {
        $this->bypassAuthorization();

        $file = File::factory()->create(['title' => 'SomeFile', 'module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $this->assertDatabaseHas('uploadfile_files', [
            'id' => $file->id,
            'title' => 'SomeFile'
        ]);

        $response = $this->deleteJson($this->userApiUrl('file/' . $file->id));
        $response->assertStatus(200);

        $this->assertSoftDeleted('uploadfile_files', [
            'id' => $file->id,
            'title' => 'SomeFile'
        ]);
    }

    /** @test */
    public function destroy_returns_the_deleted_file()
    {
        $this->bypassAuthorization();
        $now = Carbon::now();

        $file = File::factory()->create(['title' => 'SomeFile', 'module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        Carbon::setTestNow($now);

        $response = $this->deleteJson($this->userApiUrl('file/' . $file->id));
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $file->id,
            'title' => 'SomeFile',
            'deleted_at' => $now->format('Y-m-d H:i:s')
        ]);
    }

    /** @test */
    public function destroy_fires_an_event(){
        Event::fake(DocumentDeleted::class);
        $this->bypassAuthorization();

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $response = $this->deleteJson($this->userApiUrl('file/' . $file->id));
        $response->assertStatus(200);

        Event::assertDispatched(DocumentDeleted::class, function($event) use ($file) {
            return $event instanceof DocumentDeleted && $event->file->is($file);
        });
    }

    /** @test */
    public function show_returns_403_if_permission_not_given()
    {
        $this->revokePermissionTo('uploadfile.file.index');

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $response = $this->getJson($this->userApiUrl('file/' . $file->id));
        $response->assertStatus(403);
    }

    /** @test */
    public function show_returns_a_403_if_the_file_does_not_belong_to_the_activity_instance(){
        $this->bypassAuthorization();

        $activityInstance = ActivityInstance::factory()->create(['activity_id' => $this->getActivity()->id]);
        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $activityInstance->id]);

        $response = $this->getJson($this->userApiUrl('/file/' . $file->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function show_returns_200_if_permission_given()
    {
        $this->givePermissionTo('uploadfile.file.index');

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $response = $this->getJson($this->userApiUrl('file/' . $file->id));
        $response->assertStatus(200);
    }

    /** @test */
    public function show_returns_a_file()
    {
        $this->bypassAuthorization();

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $response = $this->getJson($this->userApiUrl('file/' . $file->id));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $file->id,
            'title' => $file->title,
            'description' => $file->description,
            'filename' => $file->filename
        ]);
    }

    /** @test */
    public function show_returns_a_404_if_file_not_found()
    {
        $this->bypassAuthorization();
        $this->assertDatabaseMissing('uploadfile_files', ['id' => 100]);
        $response = $this->getJson($this->userApiUrl('file/100'));
        $response->assertStatus(404);
    }


    /** @test */
    public function update_returns_403_if_permission_not_given()
    {
        $this->revokePermissionTo('uploadfile.file.update');

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $response = $this->patchJson($this->userApiUrl('file/' . $file->id), [
            'title' => 'NewTitle', 'description' => 'NewDescription'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function update_returns_a_403_if_the_file_does_not_belong_to_the_activity_instance(){
        $this->bypassAuthorization();

        $activityInstance = ActivityInstance::factory()->create(['activity_id' => $this->getActivity()->id]);
        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $activityInstance->id]);

        $response = $this->patchJson($this->userApiUrl('/file/' . $file->id), ['title' => 'TestTitle']);

        $response->assertStatus(403);
    }

    /** @test */
    public function update_returns_200_if_permission_given()
    {
        $this->givePermissionTo('uploadfile.file.update');

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $response = $this->patchJson($this->userApiUrl('file/' . $file->id), [
            'title' => 'NewTitle', 'description' => 'NewDescription'
        ]);
        $response->assertStatus(200);
    }

    /** @test */
    public function update_updates_the_title_and_description()
    {
        $this->givePermissionTo('uploadfile.file.update');

        $file = File::factory()->create(['title' => 'OldTitle', 'description' => 'OldDescription', 'module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $this->assertDatabaseHas('uploadfile_files', [
            'id' => $file->id, 'title' => 'OldTitle', 'description' => 'OldDescription'
        ]);

        $response = $this->patchJson($this->userApiUrl('file/' . $file->id), [
            'title' => 'NewTitle', 'description' => 'NewDescription'
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('uploadfile_files', [
            'id' => $file->id, 'title' => 'NewTitle', 'description' => 'NewDescription'
        ]);
    }

    /** @test */
    public function update_updates_just_the_title_if_no_description_given()
    {
        $this->givePermissionTo('uploadfile.file.update');

        $file = File::factory()->create(['title' => 'OldTitle', 'description' => 'OldDescription', 'module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $this->assertDatabaseHas('uploadfile_files', [
            'id' => $file->id, 'title' => 'OldTitle', 'description' => 'OldDescription'
        ]);

        $response = $this->patchJson($this->userApiUrl('file/' . $file->id), [
            'title' => 'NewTitle'
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('uploadfile_files', [
            'id' => $file->id, 'title' => 'NewTitle', 'description' => 'OldDescription'
        ]);
    }

    /** @test */
    public function update_updates_just_the_description_if_no_title_given()
    {
        $this->givePermissionTo('uploadfile.file.update');

        $file = File::factory()->create(['title' => 'OldTitle', 'description' => 'OldDescription', 'module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $this->assertDatabaseHas('uploadfile_files', [
            'id' => $file->id, 'title' => 'OldTitle', 'description' => 'OldDescription'
        ]);

        $response = $this->patchJson($this->userApiUrl('file/' . $file->id), [
            'description' => 'NewDescription'
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('uploadfile_files', [
            'id' => $file->id, 'title' => 'OldTitle', 'description' => 'NewDescription'
        ]);
    }

    /** @test */
    public function update_returns_the_file_with_the_new_title_and_description()
    {
        $this->givePermissionTo('uploadfile.file.update');

        $file = File::factory()->create(['title' => 'OldTitle', 'description' => 'OldDescription',
            'module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);

        $response = $this->patchJson($this->userApiUrl('file/' . $file->id), [
            'title' => 'NewTitle', 'description' => 'NewDescription'
        ]);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $file->id,
            'title' => 'NewTitle',
            'description' => 'NewDescription'
        ]);

    }

    /** @test */
    public function update_fires_an_event(){
        Event::fake(DocumentUpdated::class);
        $this->bypassAuthorization();

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $response = $this->patchJson($this->userApiUrl('file/' . $file->id), [
            'title' => 'NewTitle', 'description' => 'NewDescription'
        ]);        $response->assertStatus(200);

        Event::assertDispatched(DocumentUpdated::class, function($event) use ($file) {
            return $event instanceof DocumentUpdated && $event->file->is($file);
        });
    }

    /** @test */
    public function store_allows_a_single_file_to_be_uploaded()
    {
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => ['jpg', 'png'], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);

        $this->bypassAuthorization();

        Storage::fake();
        $file = UploadedFile::fake()->create('filename.png', 58, 'image/png');

        $response = $this->postJson($this->userApiUrl('/file'), [
            'file' => [$file],
            'title' => 'ATitle',
            'description' => 'ADescription'
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('uploadfile_files', [
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename.png',
            'mime' => 'image/png',
            'uploaded_by' => $this->getControlUser()->id(),
            'activity_instance_id' => $this->getActivityInstance()->id,
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

        $this->bypassAuthorization();

        Storage::fake();
        $file = UploadedFile::fake()->create('filename.png', 58, 'image/png');

        $response = $this->postJson($this->userApiUrl('/file'), [
            'file' => [$file],
            'title' => 'ATitle',
            'description' => 'ADescription'
        ]);
        $response->assertStatus(200);
        $response->assertJsonCount(1);

        $response->assertJsonFragment([
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename.png',
            'mime' => 'image/png',
        ]);
    }

    /** @test */
    public function store_allows_multiple_files_to_be_uploaded()
    {
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => ['jpg', 'png'], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);

        $this->bypassAuthorization();

        Storage::fake();

        $file1 = UploadedFile::fake()->create('filename.png', 58, 'image/png');
        $file2 = UploadedFile::fake()->create('filename2.png', 58, 'image/png');

        $response = $this->postJson($this->userApiUrl('/file'), [
            'file' => [$file1, $file2],
            'title' => 'ATitle',
            'description' => 'ADescription'
        ]);
        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename.png',
            'mime' => 'image/png',
        ]);
        $response->assertJsonFragment([
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename2.png',
            'mime' => 'image/png',
        ]);

        $this->assertDatabaseHas('uploadfile_files', [
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename.png',
            'mime' => 'image/png',
            'uploaded_by' => $this->getControlUser()->id(),
            'activity_instance_id' => $this->getActivityInstance()->id,
            'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        $this->assertDatabaseHas('uploadfile_files', [
            'title' => 'ATitle',
            'description' => 'ADescription',
            'filename' => 'filename2.png',
            'mime' => 'image/png',
            'uploaded_by' => $this->getControlUser()->id(),
            'activity_instance_id' => $this->getActivityInstance()->id,
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

        $this->givePermissionTo('uploadfile.file.store');

        Storage::fake();
        $file = UploadedFile::fake()->create('filename.png', 58, 'image/png');

        $response = $this->postJson($this->userApiUrl('/file'), [
            'file' => [$file],
            'title' => 'ATitle',
            'description' => 'ADescription'
        ]);
        $response->assertStatus(200);
    }

    /** @test */
    public function store_returns_a_403_if_the_permission_is_not_owned()
    {
        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => ['jpg', 'png'], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);

        $this->revokePermissionTo('uploadfile.file.store');

        Storage::fake();
        $file = UploadedFile::fake()->create('filename.png', 58, 'image/png');

        $response = $this->postJson($this->userApiUrl('/file'), [
            'file' => [$file],
            'title' => 'ATitle',
            'description' => 'ADescription'
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

        $this->bypassAuthorization();

        Storage::fake();
        $file = UploadedFile::fake()->create('filename.png', 58, 'image/png');

        $response = $this->postJson($this->userApiUrl('/file'), [
            'file' => [$file],
            'title' => 'ATitle',
            'description' => 'ADescription'
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

        $this->bypassAuthorization();

        Storage::fake();

        $file1 = UploadedFile::fake()->create('filename.png', 58, 'image/png');
        $file2 = UploadedFile::fake()->create('filename2.png', 58, 'image/png');

        $response = $this->postJson($this->userApiUrl('/file'), [
            'file' => [$file1, $file2],
            'title' => 'ATitle',
            'description' => 'ADescription'
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

        ModuleInstanceSetting::create([
            'key' => 'allowed_extensions', 'value' => [], 'module_instance_id' => $this->getModuleInstance()->id()
        ]);

        $this->bypassAuthorization();

        Storage::fake();

        $file1 = UploadedFile::fake()->create('filename.png', 58, 'image/png');
        $response = $this->postJson($this->userApiUrl('/file'), [
            'file' => [$file1],
            'title' => 'ATitle',
            'description' => 'ADescription'
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['file.0' => 'file of type:']);

    }



}

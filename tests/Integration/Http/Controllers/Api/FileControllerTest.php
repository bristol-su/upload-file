<?php

namespace BristolSU\Module\Tests\UploadFile\Integration\Http\Controllers\Api;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileControllerTest extends TestCase
{

    /** @test */
    public function store_authorizes_the_request(){
        Storage::fake('uploadfile');
        $this->assertRequiresAuthorization('post', $this->apiUrl('files'), 'file.store', [
            'title' => 'Title',
            'file' => UploadedFile::fake()->create('somename', 1)
        ]);
    }
    
    /** @test */
    public function store_creates_a_metadata_entry_to_the_database(){
        Storage::fake('uploadfile');
        $this->bypassAuthorization();
        $file = UploadedFile::fake()->create('filename.txt');
        $response = $this->post($this->apiUrl('files'), [
            'title' => 'Some Title',
            'file' => $file
        ]);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('uploadfile_files', [
            'title' => 'Some Title',
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $this->user->id,
            'module_instance_id' => $this->moduleInstance->id
        ]);
    }
    
    /** @test */
    public function store_returns_a_json_response_with_uploadedBy(){
        Storage::fake('uploadfile');
        $this->bypassAuthorization();
        $file = UploadedFile::fake()->create('filename.txt');
        $response = $this->post($this->apiUrl('files'), [
            'title' => 'Some Title',
            'file' => $file
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'title' => 'Some Title',
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $this->user->toArray(),
            'module_instance_id' => $this->moduleInstance->id
        ]);
    }
    
    /** @test */
    public function store_stores_the_file(){
        Storage::fake('uploadfile');
        
        $this->bypassAuthorization();
        $file = UploadedFile::fake()->create('filename.txt');
        $response = $this->post($this->apiUrl('files'), [
            'title' => 'Some Title',
            'file' => $file
        ]);
        
        $fileModel = File::firstOrFail();
        Storage::assertExists($fileModel->path);
        
    }
    
    /** @test */
    public function index_requests_authorization(){
        $this->assertRequiresAuthorization('get', $this->apiUrl('files'), 'file.index');
    }
    
    /** @test */
    public function index_returns_all_files_belonging_to_the_resource(){
        $this->bypassAuthorization();
        $files = factory(File::class, 5)->create(['resource_id' => $this->user->id, 'resource_type' => 'user']);
        $otherFiles = factory(File::class, 5)->create(['resource_id' => 1, 'resource_type' => 'group']);
        $response = $this->get($this->apiUrl('files'));

        $response->assertJson($files->load('uploadedBy')->toArray());
        $response->assertJsonMissing($otherFiles->load('uploadedBy')->toArray());
    }
    
}
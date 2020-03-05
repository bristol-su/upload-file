<?php

namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\Participant;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DownloadFileControllerTest extends TestCase
{

    /** @test */
    public function a_403_code_is_returned_if_the_permission_is_not_owned(){
        $this->revokePermissionTo('uploadfile.file.download');
        
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        
        $response = $this->get($this->userUrl('/file/' . $file->id . '/download'));
        $response->assertStatus(403);
    }
    
    /** @test */
    public function a_404_code_is_returned_if_the_file_is_not_found(){
        $this->bypassAuthorization();

        $response = $this->get($this->userUrl('/file/100/download'));
        $response->assertStatus(404);
    }
    
    /** @test */
    public function a_404_code_is_returned_if_the_file_is_not_found_in_storage(){
        $this->bypassAuthorization();
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->get($this->userUrl('/file/' . $file->id . '/download'));
        $response->assertStatus(404);
    }
    
    /** @test */
    public function a_download_response_is_returned_if_the_file_is_returned(){
        $this->bypassAuthorization();
        
        Storage::fake();
        $path = Storage::disk()->put('test', UploadedFile::fake()->create('test.png'));
        
        $this->assertNotEquals('TestFilename.png', $path);
        
        $file = factory(File::class)->create([
            'path' => $path,
            'filename' => 'TestFilename.png',
            'module_instance_id' => $this->getModuleInstance()->id()
        ]);

        $response = $this->get($this->userUrl('/file/' . $file->id . '/download'));
        
                
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('Content-Disposition', 'attachment; filename=TestFilename.png');
    }
    
    
    
}
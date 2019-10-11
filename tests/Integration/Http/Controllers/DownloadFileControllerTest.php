<?php

namespace BristolSU\Module\Tests\UploadFile\Integration\Http\Controllers;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\User\User;
use Illuminate\Support\Facades\Storage;

class DownloadFileControllerTest extends TestCase
{

    /** @test */
    public function download_authorizes_against_ability() {
        $this->be(factory(User::class)->create());
        $file = factory(File::class)->create();
        Storage::fake('uploadfile');
        Storage::put($file->path, 'File content');
        
        $this->assertRequiresAuthorization('get', $this->userUrl('/files/ ' . $file->id . '/download'),  'file.download');
    }

    /** @test */
    public function download_returns_the_response_of_the_storage_download_function()
    {
        $this->bypassAuthorization();
        $file = factory(File::class)->create();
        Storage::fake('uploadfile');
        Storage::put($file->path, 'File content');
        
        $response = $this->get($this->userUrl('/files/ ' . $file->id . '/download'));
        $response->assertHeader('Content-Disposition', 'attachment; filename=' . $file->filename);
        $this->assertEquals('File content', 
            $response->streamedContent()
        , sprintf('File content [%s] different than expected', $response->streamedContent()));
    }
    
    /** @test */
    public function download_returns_a_404_error_if_file_not_found(){
        $this->bypassAuthorization();
        $file = factory(File::class)->create();

        $response = $this->get($this->userUrl('/files/ ' . $file->id . '/download'));
        $response->assertStatus(404);
    }
    
}
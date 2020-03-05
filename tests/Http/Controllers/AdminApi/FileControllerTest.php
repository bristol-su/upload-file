<?php

namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\AdminApi;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\File;

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
}
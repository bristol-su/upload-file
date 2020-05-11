<?php

namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\Participant;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DownloadFileControllerTest extends TestCase
{

    /** @test */
    public function a_403_code_is_returned_if_the_permission_is_not_owned()
    {
        $this->revokePermissionTo('uploadfile.file.download');

        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->get($this->userUrl('/file/' . $file->id . '/download'));
        $response->assertStatus(403);
    }

    /** @test */
    public function a_404_code_is_returned_if_the_file_is_not_found()
    {
        $this->bypassAuthorization();

        $response = $this->get($this->userUrl('/file/100/download'));
        $response->assertStatus(404);
    }

    /** @test */
    public function a_404_code_is_returned_if_the_file_is_not_found_in_storage()
    {
        $this->bypassAuthorization();
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->get($this->userUrl('/file/' . $file->id . '/download'));
        $response->assertStatus(404);
    }

    /** @test */
    public function a_download_response_is_returned_if_the_file_is_returned()
    {
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

    /** @test */
    public function a_403_code_is_returned_if_the_permission_is_not_owned_for_old_files()
    {
        $this->revokePermissionTo('uploadfile.file.download');
        $oldModuleInstance = factory(ModuleInstance::class)->create();
        $oldActivityInstance = factory(ActivityInstance::class)->create([
            'activity_id' => $oldModuleInstance->activity->id,
            'resource_type' => 'user',
            'resource_id' => $this->getControlUser()->id()
        ]);
        $file = factory(File::class)->create(['module_instance_id' => $oldModuleInstance->id, 'activity_instance_id' => $oldActivityInstance->id]);

        $response = $this->get($this->userUrl('/old-file/' . $file->id . '/download'));
        $response->assertStatus(403);
    }

    /** @test */
    public function a_404_code_is_returned_if_the_file_is_not_found_for_old_files()
    {
        $this->bypassAuthorization();

        $response = $this->get($this->userUrl('/old-file/100/download'));
        $response->assertStatus(404);
    }

    /** @test */
    public function a_404_code_is_returned_if_the_file_is_not_found_in_storage_for_old_files()
    {
        $this->bypassAuthorization();
        $oldModuleInstance = factory(ModuleInstance::class)->create();
        $oldActivityInstance = factory(ActivityInstance::class)->create([
            'activity_id' => $oldModuleInstance->activity->id,
            'resource_type' => 'user',
            'resource_id' => $this->getControlUser()->id()
        ]);
        $file = factory(File::class)->create(['module_instance_id' => $oldModuleInstance->id, 'activity_instance_id' => $oldActivityInstance->id]);

        $response = $this->get($this->userUrl('/old-file/' . $file->id . '/download'));
        $response->assertStatus(404);
    }

    /** @test */
    public function a_download_response_is_returned_if_the_file_is_returned_for_old_files()
    {
        $this->bypassAuthorization();

        Storage::fake();
        $path = Storage::disk()->put('test', UploadedFile::fake()->create('test.png'));

        $this->assertNotEquals('TestFilename.png', $path);

        $oldModuleInstance = factory(ModuleInstance::class)->create();
        $oldActivityInstance = factory(ActivityInstance::class)->create([
            'activity_id' => $oldModuleInstance->activity->id,
            'resource_type' => 'user',
            'resource_id' => $this->getControlUser()->id()
        ]);
        $file = factory(File::class)->create([
            'module_instance_id' => $oldModuleInstance->id,
            'activity_instance_id' => $oldActivityInstance->id,
            'path' => $path,
            'filename' => 'TestFilename.png'
        ]);
        
        $response = $this->get($this->userUrl('/old-file/' . $file->id . '/download'));


        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('Content-Disposition', 'attachment; filename=TestFilename.png');
    }

    /** @test */
    public function a_404_code_is_returned_if_the_file_is_not_in_the_current_module_instance()
    {
        $this->bypassAuthorization();
        $oldModuleInstance = factory(ModuleInstance::class)->create();
        $oldActivityInstance = factory(ActivityInstance::class)->create([
            'activity_id' => $oldModuleInstance->activity->id,
            'resource_type' => 'user',
            'resource_id' => $this->getControlUser()->id()
        ]);
        $file = factory(File::class)->create(['module_instance_id' => $oldModuleInstance->id, 'activity_instance_id' => $oldActivityInstance->id]);

        $response = $this->get($this->userUrl('/file/' . $file->id . '/download'));
        $response->assertStatus(404);
    }

    /** @test */
    public function a_404_code_is_returned_if_the_file_activity_instance_does_not_belong_to_the_resource_for_old_files()
    {
        $oldUser = $this->newUser();
        $this->bypassAuthorization();
        $oldModuleInstance = factory(ModuleInstance::class)->create();
        $oldActivityInstance = factory(ActivityInstance::class)->create([
            'activity_id' => $oldModuleInstance->activity->id,
            'resource_type' => 'user',
            'resource_id' => $oldUser->id()
        ]);
        $file = factory(File::class)->create(['module_instance_id' => $oldModuleInstance->id, 'activity_instance_id' => $oldActivityInstance->id]);

        $response = $this->get($this->userUrl('/old-file/' . $file->id . '/download'));
        $response->assertStatus(404);
    }


}
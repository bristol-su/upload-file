<?php

namespace BristolSU\Module\Tests\UploadFile\Unit\Models;

use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use BristolSU\Support\User\User;

class FileTest extends TestCase
{

    /** @test */
    public function a_file_can_be_created()
    {
        $file = factory(File::class)->create(['uploaded_by' => $this->user->id, 'resource_id' => $this->user->id]);
        $this->assertDatabaseHas('uploadfile_files', [
            'title' => $file->title,
            'id' => $file->id
        ]);
    }

    /** @test */
    public function moduleInstance_returns_the_file_module_instance()
    {
        $moduleInstance = factory(ModuleInstance::class)->create();
        $file = factory(File::class)->create([
            'module_instance_id' => $moduleInstance->id,
            'uploaded_by' => $this->user->id,
            'resource_id' => $this->user->id
        ]);

        $this->assertModelEquals($moduleInstance, $file->moduleInstance);
    }

    /** @test */
    public function uploadedBy_returns_the_user_who_uploaded_the_file()
    {
        $this->beUser($this->user);
        $file = factory(File::class)->create([
            'uploaded_by' => $this->user->id,
            'resource_id' => $this->user->id
        ]);

        $this->assertEquals($this->user, $file->uploaded_by);
    }

}
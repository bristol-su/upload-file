<?php

namespace BristolSU\Module\Tests\UploadFile\Models;

use BristolSU\ControlDB\Models\User;
use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Module\UploadFile\Models\FileStatus;

class StatusTest extends TestCase
{

    /** @test */
    public function a_file_status_can_be_created(){
        $file = File::factory()->create();
        $user = $this->newUser();

        $status = FileStatus::factory()->create([
            'file_id' => $file->id,
            'created_by' => $user->id(),
            'status' => 'Awaiting Approval'
        ]);

        $this->assertDatabaseHas('uploadfile_file_statuses', [
            'file_id' => $file->id,
            'created_by' => $user->id(),
            'status' => 'Awaiting Approval'
        ]);
    }

    /** @test */
    public function a_status_belongs_to_a_file(){
        $file = File::factory()->create();

        $status = FileStatus::factory()->create([
            'file_id' => $file->id,
        ]);

        $this->assertInstanceOf(File::class, $status->file);
        $this->assertModelEquals($file, $status->file);
    }

    /** @test */
    public function created_by_attribute_returns_a_user(){
        $user = $this->newUser();

        $status = FileStatus::factory()->create([
            'created_by' => $user->id()
        ]);

        $this->assertInstanceOf(User::class, $status->created_by);
        $this->assertModelEquals($user, $status->created_by);
    }
}

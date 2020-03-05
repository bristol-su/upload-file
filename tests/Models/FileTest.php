<?php

namespace BristolSU\Module\Tests\UploadFile\Models;

use BristolSU\ControlDB\Models\DataUser;
use BristolSU\ControlDB\Models\User;
use BristolSU\Module\UploadFile\Models\Comment;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\FileStatus;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use BristolSU\Support\ModuleInstance\Settings\ModuleInstanceSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class FileTest extends TestCase
{

    /** @test */
    public function a_file_can_be_created()
    {
        $user = $this->newUser();
        $file = factory(File::class)->create(['uploaded_by' => $user->id()]);
        $this->assertDatabaseHas('uploadfile_files', [
            'title' => $file->title,
            'id' => $file->id,
            'uploaded_by' => $user->id()
        ]);
    }

    /** @test */
    public function moduleInstance_returns_the_file_module_instance()
    {
        $moduleInstance = factory(ModuleInstance::class)->create();
        $file = factory(File::class)->create([
            'module_instance_id' => $moduleInstance->id,
            'uploaded_by' => $this->newUser()->id(),
        ]);

        $this->assertInstanceOf(ModuleInstance::class, $file->moduleInstance());
        $this->assertModelEquals($moduleInstance, $file->moduleInstance());
    }

    /** @test */
    public function uploadedBy_returns_the_user_who_uploaded_the_file()
    {
        $user = $this->newUser();
        $file = factory(File::class)->create(['uploaded_by' => $user->id()]);

        $this->assertInstanceOf(User::class, $file->uploaded_by);
        $this->assertModelEquals($user, $file->uploaded_by);
    }
    
    /** @test */
    public function activityInstance_returns_the_activity_instance_of_the_file(){
        $activityInstance = factory(ActivityInstance::class)->create();
        $file = factory(File::class)->create([
            'activity_instance_id' => $activityInstance->id,
            'uploaded_by' => $this->newUser()->id(),
        ]);

        $this->assertInstanceOf(ActivityInstance::class, $file->activityInstance());
        $this->assertModelEquals($activityInstance, $file->activityInstance());
    }
    
    /** @test */
    public function it_has_a_relationship_with_statuses(){
        $file = factory(File::class)->create();
        $statuses = factory(FileStatus::class, 5)->create(['file_id' => $file->id]);
        $otherStatuses = factory(FileStatus::class, 2)->create();
        
        $resolvedStatuses = $file->statuses;
        $this->assertCount(5, $resolvedStatuses);
        $this->assertInstanceOf(Collection::class, $resolvedStatuses);
        $this->assertContainsOnlyInstancesOf(FileStatus::class, $resolvedStatuses);
        foreach($statuses as $status) {
            $this->assertModelEquals($status, $resolvedStatuses->shift());
        }
    }
    
    /** @test */
    public function status_attribute_returns_the_latest_status(){
        $file = factory(File::class)->create();
        
        factory(FileStatus::class)->create(['file_id' => $file->id, 'status' => 'Awaiting Approval', 'created_at' => Carbon::now()->subSecond(), 'updated_at' => Carbon::now()->subSecond()]);
        $this->assertEquals('Awaiting Approval', $file->status);
        
        factory(FileStatus::class)->create(['file_id' => $file->id, 'status' => 'Rejected', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $this->assertEquals('Rejected', $file->status);
        
        factory(FileStatus::class)->create(['file_id' => $file->id, 'status' => 'Approved', 'created_at' => Carbon::now()->addSecond(), 'updated_at' => Carbon::now()->addSecond()]);
        $this->assertEquals('Approved', $file->status);
    }

    /** @test */
    public function default_status_setting_is_returned_if_no_status_set(){
        factory(ModuleInstanceSetting::class)->create([
            'module_instance_id' => $this->getModuleInstance()->id(),
            'key' => 'initial_status',
            'value' => 'Some Custom Default Status'
        ]);
        
        $file = factory(File::class)->create();
        
        $this->assertEquals('Some Custom Default Status', $file->status);
    }

    /** @test */
    public function first_status_in_config_is_returned_if_no_status_set_and_no_initial_status_set(){
        $file = factory(File::class)->create();

        Config::set('uploadfile.statuses', ['Approved', 'Awaiting Approval']);
        $this->assertEquals('Approved', $file->status);

        Config::set('uploadfile.statuses', ['Something Else', 'Approved', 'Awaiting Approval']);
        $this->assertEquals('Something Else', $file->status);
    }

    /** @test */
    public function awaiting_approval_is_returned_if_config_not_set(){
        $file = factory(File::class)->create();
        Config::set('uploadfile.statuses', null);
        $this->assertEquals('Awaiting Approval', $file->status);
    }
    
    /** @test */
    public function awaiting_approval_is_returned_if_statuses_in_config_are_an_empty_array(){
        $file = factory(File::class)->create();
        Config::set('uploadfile.statuses', []);
        $this->assertEquals('Awaiting Approval', $file->status);
    }

    /** @test */
    public function it_has_a_relationship_with_comments(){
        $file = factory(File::class)->create();
        $comments = factory(Comment::class, 5)->create(['file_id' => $file->id]);
        $otherComments = factory(Comment::class, 2)->create();

        $resolvedComments = $file->comments;
        $this->assertCount(5, $resolvedComments);
        $this->assertInstanceOf(Collection::class, $resolvedComments);
        $this->assertContainsOnlyInstancesOf(Comment::class, $resolvedComments);
        foreach($comments as $status) {
            $this->assertModelEquals($status, $resolvedComments->shift());
        }
    }
    
}
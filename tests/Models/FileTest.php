<?php

namespace BristolSU\Module\Tests\UploadFile\Models;

use BristolSU\ControlDB\Models\User;
use BristolSU\Module\UploadFile\Models\Comment;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\FileStatus;
use BristolSU\Support\Activity\Activity;
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
        $file = File::factory()->create(['uploaded_by' => $user->id()]);
        $this->assertDatabaseHas('uploadfile_files', [
            'title' => $file->title,
            'id' => $file->id,
            'uploaded_by' => $user->id()
        ]);
    }

    /** @test */
    public function moduleInstance_returns_the_file_module_instance()
    {
        $moduleInstance = ModuleInstance::factory()->create();
        $file = File::factory()->create([
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
        $file = File::factory()->create(['uploaded_by' => $user->id()]);

        $this->assertInstanceOf(User::class, $file->uploaded_by);
        $this->assertModelEquals($user, $file->uploaded_by);
    }

    /** @test */
    public function activityInstance_returns_the_activity_instance_of_the_file(){
        $activityInstance = ActivityInstance::factory()->create();
        $file = File::factory()->create([
            'activity_instance_id' => $activityInstance->id,
            'uploaded_by' => $this->newUser()->id(),
        ]);

        $this->assertInstanceOf(ActivityInstance::class, $file->activityInstance());
        $this->assertModelEquals($activityInstance, $file->activityInstance());
    }

    /** @test */
    public function it_has_a_relationship_with_statuses(){
        $file = File::factory()->create();
        $statuses = FileStatus::factory()->count(5)->create(['file_id' => $file->id]);
        $otherStatuses = FileStatus::factory()->count(2)->create();

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
        $file = File::factory()->create();

        FileStatus::factory()->create(['file_id' => $file->id, 'status' => 'Awaiting Approval', 'created_at' => Carbon::now()->subSecond(), 'updated_at' => Carbon::now()->subSecond()]);
        $this->assertEquals('Awaiting Approval', $file->status);

        FileStatus::factory()->create(['file_id' => $file->id, 'status' => 'Rejected', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $this->assertEquals('Rejected', $file->status);

        FileStatus::factory()->create(['file_id' => $file->id, 'status' => 'Approved', 'created_at' => Carbon::now()->addSecond(), 'updated_at' => Carbon::now()->addSecond()]);
        $this->assertEquals('Approved', $file->status);
    }

    /** @test */
    public function default_status_setting_is_returned_if_no_status_set(){
        ModuleInstanceSetting::factory()->create([
            'module_instance_id' => $this->getModuleInstance()->id(),
            'key' => 'initial_status',
            'value' => 'Some Custom Default Status'
        ]);

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $this->assertEquals('Some Custom Default Status', $file->status);
    }

    /** @test */
    public function first_status_in_config_is_returned_if_no_status_set_and_no_initial_status_set(){
        $file = File::factory()->create();

        Config::set('uploadfile.statuses', ['Approved', 'Awaiting Approval']);
        $this->assertEquals('Approved', $file->status);

        Config::set('uploadfile.statuses', ['Something Else', 'Approved', 'Awaiting Approval']);
        $this->assertEquals('Something Else', $file->status);
    }

    /** @test */
    public function awaiting_approval_is_returned_if_config_not_set(){
        $file = File::factory()->create();
        Config::set('uploadfile.statuses', null);
        $this->assertEquals('Awaiting Approval', $file->status);
    }

    /** @test */
    public function awaiting_approval_is_returned_if_statuses_in_config_are_an_empty_array(){
        $file = File::factory()->create();
        Config::set('uploadfile.statuses', []);
        $this->assertEquals('Awaiting Approval', $file->status);
    }

    /** @test */
    public function it_has_a_relationship_with_comments(){
        $file = File::factory()->create();
        $comments = Comment::factory()->count(5)->create(['file_id' => $file->id]);
        $otherComments = Comment::factory()->count(2)->create();

        $resolvedComments = $file->comments;
        $this->assertCount(5, $resolvedComments);
        $this->assertInstanceOf(Collection::class, $resolvedComments);
        $this->assertContainsOnlyInstancesOf(Comment::class, $resolvedComments);
        foreach($comments as $status) {
            $this->assertModelEquals($status, $resolvedComments->shift());
        }
    }

    /** @test */
    public function it_has_tags(){
        $file = File::factory()->create([
            'tags' => ['tag1', 'tag2']
        ]);
        $this->assertEquals(['tag1', 'tag2'], $file->tags);
    }

    /** @test */
    public function withTag_returns_all_files_uploaded_by_the_participant_with_a_tag_for_a_user(){
        $user1 = $this->getControlUser();
        $user2 = User::factory()->create();
        $activity1 = Activity::factory()->create();
        $activity2 = Activity::factory()->create();
        $activityInstance1 = ActivityInstance::factory()->create(['activity_id' => $activity1->id, 'resource_id' => $user1, 'resource_type' => 'user']);
        $activityInstance2 = ActivityInstance::factory()->create(['activity_id' => $activity2->id, 'resource_id' => $user1, 'resource_type' => 'user']);
        $activityInstance3 = ActivityInstance::factory()->create(['activity_id' => $activity1->id, 'resource_id' => $user2, 'resource_type' => 'user']);

        $files = File::factory()->count(3)->create(['activity_instance_id' => $activityInstance1->id, 'tags' => ['w', 'needed']])->merge(
            File::factory()->count(2)->create(['activity_instance_id' => $activityInstance2->id, 'tags' => ['w', 'needed']])
        )->merge(
            File::factory()->count(2)->create(['activity_instance_id' => $this->getActivityInstance()->id, 'tags' => ['w', 'needed']])
        )->merge(
            File::factory()->count(2)->create(['activity_instance_id' => $activityInstance1->id, 'tags' => ['w']])
        );
        File::factory()->count(4)->create(['activity_instance_id' => $activityInstance3->id]);

        $foundFiles = File::withTag('needed')->get();

        $this->assertCount(7, $foundFiles);
        $this->assertModelEquals($files[0], $foundFiles->shift());
        $this->assertModelEquals($files[1], $foundFiles->shift());
        $this->assertModelEquals($files[2], $foundFiles->shift());
        $this->assertModelEquals($files[3], $foundFiles->shift());
        $this->assertModelEquals($files[4], $foundFiles->shift());
        $this->assertModelEquals($files[5], $foundFiles->shift());
        $this->assertModelEquals($files[6], $foundFiles->shift());

    }

    /** @test */
    public function a_file_has_an_activity_instance_appended_to_it_when_an_array(){
        $file = File::factory()->create()->toArray();
        $this->assertArrayHasKey('activity_instance', $file);
    }
}

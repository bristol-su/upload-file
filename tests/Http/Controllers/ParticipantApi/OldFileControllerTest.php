<?php

namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\ParticipantApi;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\ModuleInstance\Settings\ModuleInstanceSetting;

class OldFileControllerTest extends TestCase
{

    /** @test */
    public function it_returns_all_files_for_the_resource_with_the_tag_from_settings(){
        ModuleInstanceSetting::factory()->create([
            'module_instance_id' => $this->getModuleInstance()->id(),
            'key' => 'tags_to_merge',
            'value' => ['tag1', 'tag2'],
            'encoded' => true
        ]);

        $user = $this->getControlUser();
        $activityInstance = ActivityInstance::factory()->create(['resource_id' => $user->id(), 'resource_type' => 'user']);
        $activityInstanceOther = ActivityInstance::factory()->create(['resource_id' => $user->id(), 'resource_type' => 'group']);

        // Should return all files with tags tag1 or tag2 that belong to the user
        $files = File::factory()->count(3)->create(['activity_instance_id' => $this->getActivityInstance()->id, 'tags' => ['tag1']])
            ->merge(File::factory()->count(3)->create(['activity_instance_id' => $activityInstance->id, 'tags' => ['tag1']]))
            ->merge(File::factory()->count(3)->create(['activity_instance_id' => $activityInstance->id, 'tags' => ['tag2']]))
            ->merge(File::factory()->count(3)->create(['activity_instance_id' => $this->getActivityInstance()->id, 'tags' => ['tag1', 'tag2']]))
            ->merge(File::factory()->count(3)->create(['activity_instance_id' => $activityInstance->id, 'tags' => ['tag1', 'tag2']]));

        $otherFiles = File::factory()->count(3)->create(['activity_instance_id' => $activityInstanceOther->id, 'tags' => ['tag1']])
            ->merge(File::factory()->count(3)->create(['activity_instance_id' => $activityInstanceOther->id, 'tags' => ['tag1', 'tag2']]))
            ->merge(File::factory()->count(3)->create(['activity_instance_id' => $activityInstance->id, 'tags' => []]))
            ->merge(File::factory()->count(3)->create(['activity_instance_id' => $this->getActivityInstance()->id, 'tags' => []]))
            ->merge(File::factory()->count(3)->create(['activity_instance_id' => $activityInstance->id, 'tags' => ['tag12']]))
            ->merge(File::factory()->count(3)->create(['activity_instance_id' => $this->getActivityInstance()->id, 'tags' => ['tag22']]));

        $returnedFiles = $this->get($this->userApiUrl('/file/old'));
        $returnedFiles->assertJsonCount(15);

        $neededFileIds = $files->map(function(File $file) {
            return $file->id;
        });

        foreach($returnedFiles->decodeResponseJson()->json() as $file) {
            $this->assertContains($file['id'], $neededFileIds);
            $neededFileIds = $neededFileIds->filter(function($id) use ($file) {
                return $id !== $file['id'];
            });
        }
        $this->assertCount(0, $neededFileIds);
    }

}

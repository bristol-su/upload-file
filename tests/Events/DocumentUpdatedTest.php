<?php

namespace BristolSU\Module\Tests\UploadFile\Events;

use BristolSU\ControlDB\Models\DataUser;
use BristolSU\ControlDB\Models\User;
use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Events\DocumentUpdated;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Carbon\Carbon;

class DocumentUpdatedTest extends TestCase
{

    /** @test */
    public function it_returns_a_set_of_usable_fields(){
        $activityInstance = ActivityInstance::factory()->create();
        $moduleInstance = ModuleInstance::factory()->create();

        $dataUser = DataUser::factory()->create([
            'email' => 'someemail@example.com',
            'first_name' => 'FirstName',
            'last_name' => 'SomeThingElse',
            'preferred_name' => 'xyz Hi'
        ]);
        $user = User::factory()->create(['data_provider_id' => $dataUser->id()]);

        $createdAt = Carbon::now()->subDay();
        $updatedAt = Carbon::now()->subHour()->subMinute();

        $file = File::factory()->create([
            'uploaded_by' => $user->id(),
            'module_instance_id' => $moduleInstance->id,
            'activity_instance_id' => $activityInstance->id,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt
        ]);

        $event = new DocumentUpdated($file);

        $this->assertEquals([
            'file_id' => $file->id,
            'title' => $file->title,
            'description' => $file->description,
            'filename' => $file->filename,
            'mime' => $file->mime,
            'size' => $file->size,
            'uploaded_by_id' => $user->id(),
            'uploaded_by_email' => 'someemail@example.com',
            'uploaded_by_first_name' => 'FirstName',
            'uploaded_by_last_name' => 'SomeThingElse',
            'uploaded_by_preferred_name' => 'xyz Hi',
            'module_instance_id' => $moduleInstance->id,
            'activity_instance_id' => $activityInstance->id,
            'uploaded_at' => $createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $updatedAt->format('Y-m-d H:i:s')
        ], $event->getFields());
    }

    /** @test */
    public function it_returns_metadata_for_the_fields(){
        $file = File::factory()->create();

        $event = new DocumentUpdated($file);
        $fields = array_keys($event->getFields());

        foreach($fields as $field) {
            $this->assertArrayHasKey($field, DocumentUpdated::getFieldMetaData());
            $this->assertArrayHasKey('label', DocumentUpdated::getFieldMetaData()[$field]);
            $this->assertArrayHasKey('helptext', DocumentUpdated::getFieldMetaData()[$field]);
        }
    }

}

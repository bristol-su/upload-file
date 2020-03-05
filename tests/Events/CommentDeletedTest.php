<?php

namespace BristolSU\Module\Tests\UploadFile\Events;

use BristolSU\ControlDB\Models\DataUser;
use BristolSU\ControlDB\Models\User;
use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Events\CommentDeleted;
use BristolSU\Module\UploadFile\Models\Comment;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Carbon\Carbon;

class CommentDeletedTest extends TestCase
{

    /** @test */
    public function it_returns_a_set_of_usable_fields(){
        $activityInstance = factory(ActivityInstance::class)->create();
        $moduleInstance = factory(ModuleInstance::class)->create();
        
        $dataUser1 = factory(DataUser::class)->create([
            'email' => 'someemail@example.com',
            'first_name' => 'FirstName',
            'last_name' => 'SomeThingElse',
            'preferred_name' => 'xyz Hi'
        ]);
        $user1 = factory(User::class)->create(['data_provider_id' => $dataUser1->id()]);

        $dataUser2 = factory(DataUser::class)->create([
            'email' => 'someemail2@example.com',
            'first_name' => 'FirstName2',
            'last_name' => 'SomeThingElse2',
            'preferred_name' => 'xyz Hi2'
        ]);
        $user2 = factory(User::class)->create(['data_provider_id' => $dataUser2->id()]);
        
        $createdAt = Carbon::now()->subDay();
        $updatedAt = Carbon::now()->subHour()->subMinute();
        $deletedAt = Carbon::now()->subHour()->subMinutes(10);
        
        $file = factory(File::class)->create([
            'uploaded_by' => $user1->id(),
            'module_instance_id' => $moduleInstance->id,
            'activity_instance_id' => $activityInstance->id,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
        
        $comment = factory(Comment::class)->create([
            'file_id' => $file->id,
            'posted_by' => $user2->id(),
            'comment' => 'A Test Comment',
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'deleted_at' => $deletedAt
        ]);
        
        $event = new CommentDeleted($comment);
        
        $this->assertEquals([
            'file_id' => $file->id,
            'file_title' => $file->title,
            'file_description' => $file->description,
            'file_filename' => $file->filename,
            'file_mime' => $file->mime,
            'file_size' => $file->size,
            'file_uploaded_by_id' => $user1->id(),
            'file_uploaded_by_email' => 'someemail@example.com',
            'file_uploaded_by_first_name' => 'FirstName',
            'file_uploaded_by_last_name' => 'SomeThingElse',
            'file_uploaded_by_preferred_name' => 'xyz Hi',
            'file_module_instance_id' => $moduleInstance->id,
            'file_activity_instance_id' => $activityInstance->id,
            'file_uploaded_at' => $createdAt->format('Y-m-d H:i:s'),
            'file_updated_at' => $updatedAt->format('Y-m-d H:i:s'),
            'comment' => 'A Test Comment',
            'comment_id' => $comment->id,
            'comment_posted_at' => $createdAt->format('Y-m-d H:i:s'),
            'comment_edited_at' => $updatedAt->format('Y-m-d H:i:s'),
            'comment_posted_by_id' => $user2->id(),
            'comment_posted_by_email' => 'someemail2@example.com',
            'comment_posted_by_first_name' => 'FirstName2',
            'comment_posted_by_last_name' => 'SomeThingElse2',
            'comment_posted_by_preferred_name' => 'xyz Hi2',
            'comment_deleted_at' => $deletedAt->format('Y-m-d H:i:s')
        ], $event->getFields());
    }
    
    /** @test */
    public function it_returns_metadata_for_the_fields(){
        $comment = factory(Comment::class)->create();
        $comment->delete();
        $event = new CommentDeleted($comment);
        $fields = array_keys($event->getFields());
        
        foreach($fields as $field) {
            $this->assertArrayHasKey($field, CommentDeleted::getFieldMetaData());
            $this->assertArrayHasKey('label', CommentDeleted::getFieldMetaData()[$field]);
            $this->assertArrayHasKey('helptext', CommentDeleted::getFieldMetaData()[$field]);
        }
    }
    
}
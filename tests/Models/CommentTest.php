<?php

namespace BristolSU\Module\Tests\UploadFile\Models;

use BristolSU\ControlDB\Models\User;
use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\Comment;
use BristolSU\Module\UploadFile\Models\File;

class CommentTest extends TestCase
{

    /** @test */
    public function a_comment_can_be_created(){
        $file = factory(File::class)->create();
        $user = $this->newUser();
        
        $comment = factory(Comment::class)->create([
            'file_id' => $file->id,
            'comment' => 'This is a test comment',
            'posted_by' => $user->id()
        ]);
        
        $this->assertDatabaseHas('uploadfile_comments', [
            'id' => $comment->id,
            'file_id' => $file->id,
            'comment' => 'This is a test comment',
            'posted_by' => $user->id()
        ]);
    }
    
    /** @test */
    public function a_comment_belongs_to_a_file(){
        $file = factory(File::class)->create();

        $comment = factory(Comment::class)->create([
            'file_id' => $file->id,
        ]);
        
        $this->assertInstanceOf(File::class, $comment->file);
        $this->assertModelEquals($file, $comment->file);
    }
    
    /** @test */
    public function posted_by_attribute_returns_a_user(){
        $user = $this->newUser();

        $comment = factory(Comment::class)->create([
            'posted_by' => $user->id()
        ]);

        $this->assertInstanceOf(User::class, $comment->posted_by);
        $this->assertModelEquals($user, $comment->posted_by);
    }
    
}
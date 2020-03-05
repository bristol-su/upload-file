<?php

namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\AdminApi;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Events\CommentCreated;
use BristolSU\Module\UploadFile\Events\CommentDeleted;
use BristolSU\Module\UploadFile\Events\CommentUpdated;
use BristolSU\Module\UploadFile\Models\Comment;
use BristolSU\Module\UploadFile\Models\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;

class CommentControllerTest extends TestCase
{

    /** @test */
    public function index_returns_403_if_permission_not_given(){
        $this->revokePermissionTo('uploadfile.admin.comment.index');
        
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        
        $response = $this->getJson($this->adminApiUrl('file/' . $file->id . '/comment'));
        $response->assertStatus(403);
    }
    
    /** @test */
    public function index_returns_200_if_permission_given(){
        $this->givePermissionTo('uploadfile.admin.comment.index');

        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->getJson($this->adminApiUrl('file/' . $file->id . '/comment'));
        $response->assertStatus(200);
    }
    
    /** @test */
    public function index_returns_comments(){
        $this->bypassAuthorization();
        
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comments = factory(Comment::class, 5)->create(['file_id' => $file->id]);
        $otherComments = factory(Comment::class, 3)->create();
        
        $response = $this->getJson($this->adminApiUrl('/file/' . $file->id . '/comment'));
        
        $response->assertStatus(200);
        $response->assertJsonCount(5);
        
        foreach($comments as $comment) {
            $response->assertJsonFragment([
                'comment' => $comment->comment,
                'file_id' => (string) $file->id
            ]);
        }
    }

    /** @test */
    public function store_returns_403_if_permission_not_given(){
        $this->revokePermissionTo('uploadfile.admin.comment.store');

        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->postJson($this->adminApiUrl('file/' . $file->id . '/comment'), ['comment' => 'Test']);
        $response->assertStatus(403);
    }

    /** @test */
    public function store_returns_200_if_permission_given(){
        $this->givePermissionTo('uploadfile.admin.comment.store');

        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->postJson($this->adminApiUrl('file/' . $file->id . '/comment'), ['comment' => 'Test']);
        $response->assertStatus(201);
    }
    
    /** @test */
    public function store_creates_a_new_comment_in_the_database(){
        $this->bypassAuthorization();
        
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        
        $this->assertDatabaseMissing('uploadfile_comments', ['file_id' => $file->id]);
        
        $response = $this->postJson($this->adminApiUrl('file/' . $file->id . '/comment'), ['comment' => 'TestComment Here']);
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('uploadfile_comments', [
            'file_id' => $file->id,
            'comment' => 'TestComment Here',
            'posted_by' => $this->getControlUser()->id()
        ]);
    }
    
    /** @test */
    public function store_returns_the_new_comment(){
        $this->bypassAuthorization();

        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->postJson($this->adminApiUrl('file/' . $file->id . '/comment'), ['comment' => 'TestComment Here']);
        $response->assertStatus(201);
        $response->assertJsonFragment([
            'file_id' => $file->id,
            'comment' => 'TestComment Here',
        ]);
    }
    
    /** @test */
    public function store_fires_an_event(){
        Event::fake(CommentCreated::class);
        $this->bypassAuthorization();

        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->postJson($this->adminApiUrl('file/' . $file->id . '/comment'), ['comment' => 'TestComment Here']);
        $response->assertStatus(201);
        
        Event::assertDispatched(CommentCreated::class, function($event) use ($file) {
            return $event instanceof CommentCreated && $event->comment->file->is($file);
        });
    }
    
    /** @test */
    public function destroy_returns_403_if_permission_not_given(){
        $this->revokePermissionTo('uploadfile.admin.comment.destroy');
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comment = factory(Comment::class)->create(['file_id' => $file->id]);
        
        $response = $this->deleteJson($this->adminApiUrl('/comment/' . $comment->id));
        $response->assertStatus(403);
        
        $this->assertDatabaseHas('uploadfile_comments', [
            'id' => $comment->id,
            'deleted_at' => null
        ]);
    }
    
    /** @test */
    public function destroy_returns_200_if_permission_given(){
        $this->givePermissionTo('uploadfile.admin.comment.destroy');

        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comment = factory(Comment::class)->create(['file_id' => $file->id]);

        $response = $this->deleteJson($this->adminApiUrl('/comment/' . $comment->id));
        $response->assertStatus(200);
    }
    
    /** @test */
    public function destroy_soft_deletes_the_comment(){
        $this->bypassAuthorization();
        
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comment = factory(Comment::class)->create(['file_id' => $file->id]);
        
        $response = $this->deleteJson($this->adminApiUrl('/comment/' . $comment->id));
        $response->assertStatus(200);
        
        $this->assertSoftDeleted('uploadfile_comments', [
            'id' => $comment->id,
        ]);
    }
    
    /** @test */
    public function destroy_returns_the_comment(){
        $this->bypassAuthorization();
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comment = factory(Comment::class)->create(['file_id' => $file->id]);
        $now = Carbon::now();
        Carbon::setTestNow($now);
        
        $response = $this->deleteJson($this->adminApiUrl('/comment/' . $comment->id));
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $comment->id,
            'deleted_at' => $now->format('Y-m-d H:i:s')
        ]);
    }

    /** @test */
    public function destroy_fires_an_event(){
        Event::fake(CommentDeleted::class);
        $this->bypassAuthorization();

        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comment = factory(Comment::class)->create(['file_id' => $file->id]);
        
        $response = $this->deleteJson($this->adminApiUrl('comment/' . $comment->id));
        $response->assertStatus(200);

        Event::assertDispatched(CommentDeleted::class, function($event) use ($comment) {
            return $event instanceof CommentDeleted && $event->comment->is($comment);
        });
    }
    
    /** @test */
    public function update_returns_403_if_permission_not_owned(){
        $this->revokePermissionTo('uploadfile.admin.comment.update');
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comment = factory(Comment::class)->create(['comment' => 'OldComment', 'file_id' => $file->id]);
        
        $response = $this->patchJson($this->adminApiUrl('/comment/' . $comment->id), ['comment' => 'NewComment']);
        $response->assertStatus(403);
        
        $this->assertDatabaseHas('uploadfile_comments', [
            'id' => $comment->id,
            'comment' => 'OldComment'
        ]);
    }
    
    /** @test */
    public function update_returns_200_if_permission_owned(){
        $this->givePermissionTo('uploadfile.admin.comment.update');
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comment = factory(Comment::class)->create(['comment' => 'OldComment', 'file_id' => $file->id]);

        $response = $this->patchJson($this->adminApiUrl('/comment/' . $comment->id), ['comment' => 'NewComment']);
        $response->assertStatus(200);
    }
    
    /** @test */
    public function update_updates_the_comment_text(){
        $this->bypassAuthorization();
        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comment = factory(Comment::class)->create(['comment' => 'OldComment', 'file_id' => $file->id]);

        $response = $this->patchJson($this->adminApiUrl('/comment/' . $comment->id), ['comment' => 'NewComment']);
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('uploadfile_comments', [
            'id' => $comment->id,
            'comment' => 'NewComment'
        ]);
    }

    /** @test */
    public function update_returns_the_updated_comment(){
        $this->bypassAuthorization();

        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comment = factory(Comment::class)->create(['comment' => 'OldComment', 'file_id' => $file->id]);

        $response = $this->patchJson($this->adminApiUrl('/comment/' . $comment->id), ['comment' => 'NewComment']);
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $comment->id,
            'comment' => 'NewComment'
        ]);
    }

    /** @test */
    public function update_fires_an_event(){
        Event::fake(CommentUpdated::class);
        $this->bypassAuthorization();

        $file = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id()]);
        $comment = factory(Comment::class)->create(['file_id' => $file->id]);

        $response = $this->patchJson($this->adminApiUrl('comment/' . $comment->id), ['comment' => 'NewComment']);
        $response->assertStatus(200);

        Event::assertDispatched(CommentUpdated::class, function($event) use ($comment) {
            return $event instanceof CommentUpdated && $event->comment->is($comment);
        });
    }
}
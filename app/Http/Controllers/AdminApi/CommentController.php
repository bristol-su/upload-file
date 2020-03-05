<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\AdminApi;

use BristolSU\Module\UploadFile\Events\CommentCreated;
use BristolSU\Module\UploadFile\Events\CommentDeleted;
use BristolSU\Module\UploadFile\Events\CommentUpdated;
use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Models\Comment;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\ActivityInstance\Contracts\ActivityInstanceResolver;
use BristolSU\Support\Authentication\Contracts\Authentication;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function index(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('admin.comment.index');

        return $file->comments;
    }

    public function store(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('admin.comment.store');
        
        $comment = $file->comments()->create([
            'comment' => $request->input('comment'),
            'posted_by' => app(Authentication::class)->getUser()->id
        ]);
        
        event(new CommentCreated($comment));
        
        return $comment;
    }

    public function destroy(Request $request, Activity $activity, ModuleInstance $moduleInstance, Comment $comment)
    {
        $this->authorize('admin.comment.destroy');
        
        $comment->delete();
        
        event(new CommentDeleted($comment));
        
        return $comment;
    }

    public function update(Request $request, Activity $activity, ModuleInstance $moduleInstance, Comment $comment)
    {
        $this->authorize('admin.comment.update');
        
        $comment->comment = $request->input('comment', $comment->comment);
        
        $comment->save();

        event(new CommentUpdated($comment));

        return $comment;
    }

}
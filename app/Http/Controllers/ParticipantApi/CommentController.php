<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\ParticipantApi;

use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\Authentication\Contracts\Authentication;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function index(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('comment.index');
        
        return $file->comments;
    }

    public function store(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('comment.store');
        
        return $file->comments()->create([
            'comment' => $request->input('comment'),
            'posted_by' => app(Authentication::class)->getUser()->id
        ]);
    }

}
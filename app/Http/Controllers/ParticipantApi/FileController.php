<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\ParticipantApi;

use BristolSU\Module\UploadFile\Events\DocumentDeleted;
use BristolSU\Module\UploadFile\Events\DocumentUpdated;
use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Http\Requests\ParticipantApi\FileController\StoreRequest;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\ActivityInstance\Contracts\ActivityInstanceResolver;
use BristolSU\Support\Authentication\Contracts\Authentication;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FileController extends Controller
{
    public function store(StoreRequest $request, Authentication $authentication)
    {
        $fileMetadata = collect();

        foreach (Arr::wrap($request->file('file')) as $file) {

            $path = $file->store('uploadfile');

            $fileMetadata->push($tempFileMeta = File::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'filename' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'path' => $path,
                'size' => $file->getSize(),
                'uploaded_by' => $authentication->getUser()->id(),
            ]));

            event(new DocumentUploaded($tempFileMeta));
        }

        return $fileMetadata;
    }

    public function index(Request $request)
    {
        $this->authorize('file.index');
        return File::forResource()->with(['statuses', 'comments'])->get();
    }

    public function destroy(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('file.destroy');

        if((int) $file->activity_instance_id !== (int) app(ActivityInstanceResolver::class)->getActivityInstance()->id) {
            throw new AuthorizationException();
        }
                
        $file->delete();
        $file->refresh();

        event(new DocumentDeleted($file));
        
        return $file->refresh();
    }

    public function show(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('file.index');

        if((int) $file->activity_instance_id !== (int) app(ActivityInstanceResolver::class)->getActivityInstance()->id) {
            throw new AuthorizationException();
        }
        
        return $file->load('statuses');
    }

    public function update(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('file.update');

        if((int) $file->activity_instance_id !== (int) app(ActivityInstanceResolver::class)->getActivityInstance()->id) {
            throw new AuthorizationException();
        }
        
        $file->title = $request->input('title', $file->title);
        $file->description = $request->input('description', $file->description);

        $file->save();
            
        event(new DocumentUpdated($file));
        
        return $file;
    }

}
<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\AdminApi;

use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Http\Requests\AdminApi\FileController\StoreRequest;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\Authentication\Contracts\Authentication;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FileController extends Controller
{

    public function index()
    {
        $this->authorize('admin.file.index');
        
        return File::forModuleInstance()->with(['statuses', 'comments'])->get();
    }

    public function show(Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('admin.file.index');
        
        return $file->load(['statuses', 'comments']);
    }

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
                'activity_instance_id' => $request->input('activity_instance_id')
            ]));

            event(new DocumentUploaded($tempFileMeta));
        }

        return $fileMetadata;
    }
    
}
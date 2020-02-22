<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\ParticipantApi;

use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Http\Requests\ParticipantApi\FileController\StoreRequest;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\Authentication\Contracts\Authentication;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;

class FileController extends Controller
{
    public function store(StoreRequest $request, Authentication $authentication)
    {
        $fileMetadata = collect();
        
        foreach (Arr::wrap($request->file('file')) as $file) {

            $path = $file->store(alias());

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
        return File::forResource()->with('statuses')->get();
    }

    public function destroy(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('file.destroy');
        
        $file->delete();
        
        return $file;
    }

    public function show(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('file.index');
        
        return $file->load('statuses');
    }

    public function update(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('file.update');

        $file->title = $request->input('title', $file->title);
        $file->description = $request->input('description', $file->description);
        
        if($file->save()) {
            return $file;
        }
        return Response::make('Could not save the file', 500);
    }

}
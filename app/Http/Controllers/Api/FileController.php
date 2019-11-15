<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\Api;

use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\Authentication\Contracts\Authentication;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileController extends Controller
{
    public function store(Request $request, Authentication $authentication, Activity $activity, ModuleInstance $moduleInstance)
    {
        $this->authorize('file.store');

        $file = $request->file('file');
        $path = $file->store(alias());

        $file = File::create([
            'title' => $request->get('title'),
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'path' => $path,
            'size' => $file->getSize(),
            'uploaded_by' => $authentication->getUser()->id,
            'module_instance_id' => $moduleInstance->id,
        ]);
        event(new DocumentUploaded($file));
        return $file;
    }

    public function index(Request $request)
    {
        $this->authorize('file.index');
        return File::forResource()->get();
    }
    
}
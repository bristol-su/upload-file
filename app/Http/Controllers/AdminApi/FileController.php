<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\AdminApi;

use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\ModuleInstance\ModuleInstance;

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
    
}
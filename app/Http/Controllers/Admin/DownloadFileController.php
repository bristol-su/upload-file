<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\Admin;

use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DownloadFileController extends Controller
{

    public function download(Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('admin.file.download');
        
        if(Storage::exists($file->path)) {
            return Storage::download($file->path, $file->filename, [
                'X-Vapor-Base64-Encode' => 'True'
            ]);
        }
        
        throw new HttpException(404, 'File not found');
    }
    
}
<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\Participant;

use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DownloadFileController extends Controller
{

    public function download(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('file.download');
        
        if(Storage::exists($file->path)) {
            return Storage::download($file->path, $file->filename, [
                'X-Vapor-Base64-Encode' => 'True'
            ]);
        }
        
        throw new HttpException(404, 'File not found');
    }

    public function downloadOld(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('file.download');

        if(Storage::exists($file->path)) {
            return Storage::download($file->path, $file->filename, [
                'X-Vapor-Base64-Encode' => 'True'
            ]);
        }

        throw new HttpException(404, 'File not found');
    }
    
}
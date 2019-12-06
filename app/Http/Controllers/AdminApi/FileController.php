<?php


namespace BristolSU\Module\UploadFile\Http\Controllers\AdminApi;


use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Models\File;

class FileController extends Controller
{

    public function index()
    {
        $this->authorize('admin.file.index');
        return File::forModuleInstance()->get();
    }
    
}
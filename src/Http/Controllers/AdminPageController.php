<?php

namespace BristolSU\Module\UploadFile\Http\Controllers;


use BristolSU\Support\Permissions\Contracts\PermissionStore;

class AdminPageController extends Controller
{
    
    public function index(PermissionStore $permission)
    {
        $this->authorize('uploadfile.admin.view-page');
        return view('uploadfile::admin');
    }
    
}
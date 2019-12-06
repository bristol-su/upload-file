<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\Admin;


use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Support\Permissions\Contracts\PermissionStore;
use BristolSU\Support\Permissions\Contracts\PermissionTester;

class AdminPageController extends Controller
{
    
    public function index(PermissionStore $permission)
    {
        $this->authorize('admin.view-page');
        return view(alias() . '::admin');
    }
    
}
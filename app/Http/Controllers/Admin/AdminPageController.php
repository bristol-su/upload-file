<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\Admin;

use BristolSU\Module\UploadFile\Http\Controllers\Controller;

class AdminPageController extends Controller
{
    
    public function index()
    {
        $this->authorize('admin.view-page');
        return view(alias() . '::admin');
    }
    
}
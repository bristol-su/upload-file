<?php

namespace BristolSU\Module\UploadFile\Http\Controllers;

class ParticipantPageController extends Controller
{

    public function index()
    {
        $this->authorize('uploadfile.view-page');
        return view('uploadfile::participant');
    }
    
}
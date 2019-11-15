<?php

namespace BristolSU\Module\UploadFile\Http\Controllers;

class ParticipantPageController extends Controller
{

    public function index()
    {
        $this->authorize('view-page');
        
        return view(alias() . '::participant');
    }
    
}
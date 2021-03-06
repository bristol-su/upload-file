<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\Participant;

use BristolSU\Module\UploadFile\Http\Controllers\Controller;

class ParticipantPageController extends Controller
{

    public function index()
    {
        $this->authorize('view-page');
        
        return view(alias() . '::participant');
    }
    
}
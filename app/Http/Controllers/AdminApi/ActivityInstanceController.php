<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\AdminApi;

use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\ActivityInstance\Contracts\ActivityInstanceRepository;
use BristolSU\Support\Module\Module;

class ActivityInstanceController extends Controller
{

    public function index(Activity $activity, Module $module, ActivityInstanceRepository $activityInstanceRepository)
    {
        return $activityInstanceRepository->allForActivity($activity->id);
    }
    
}
<?php


namespace BristolSU\Module\UploadFile\Http\Controllers\AdminApi;


use BristolSU\Module\UploadFile\Events\StatusChanged;
use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\Authentication\Contracts\Authentication;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Http\Request;

class FileStatusController extends Controller
{

    public function store(Request $request, Authentication $authentication, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('admin.status.create');
        
        $status = $file->statuses()->create([
            'status' => $request->input('status'),
            'created_by' => $authentication->getUser()->id()
        ]);
        
        event(new StatusChanged($file));
        
        return $status;
    }

}
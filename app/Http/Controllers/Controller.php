<?php

namespace BristolSU\Module\UploadFile\Http\Controllers;

use BristolSU\Support\Module\Module;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

class Controller
{
    use AuthorizesRequests {
        authorize as baseAuthorize;
    }
    
    use DispatchesJobs, ValidatesRequests;

    public function authorize($ability, $arguments = [])
    {
        $moduleInstance = app()->make(ModuleInstance::class);
        return $this->baseAuthorize(
            $moduleInstance->alias . '.' . $ability,
            $arguments
        );
    }
}
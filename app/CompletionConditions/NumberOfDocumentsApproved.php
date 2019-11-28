<?php

namespace BristolSU\Module\UploadFile\CompletionConditions;

use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\Completion\Contracts\CompletionCondition;
use BristolSU\Support\ModuleInstance\Contracts\ModuleInstance;

class NumberOfDocumentsApproved implements CompletionCondition
{

    public function isComplete($settings, ActivityInstance $activityInstance, ModuleInstance $moduleInstance): bool
    {
        // TODO Where approved
        return File::forResource($activityInstance->id, $moduleInstance->id())->count() >= $settings['number_of_files'];
    }

    public function options(): array
    {
        return [
            'number_of_files' => 1
        ];
    }
    
}
<?php

namespace BristolSU\Module\UploadFile\CompletionConditions;

use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\Completion\Contracts\CompletionCondition;
use BristolSU\Support\ModuleInstance\Contracts\ModuleInstance;

class NumberOfDocumentsSubmitted extends CompletionCondition
{
    
    public function isComplete($settings, ActivityInstance $activityInstance, ModuleInstance $moduleInstance): bool
    {
        return File::forResource($activityInstance->id, $moduleInstance->id())->count() >= $settings['number_of_files'];
    }

    public function options(): array
    {
        return [
            'number_of_files' => 1
        ];
    }

    public function name(): string
    {
        return 'A number of documents have been submitted';
    }

    public function description(): string
    {
        return 'Complete when a given number of documents have been submitted';
    }

    public function alias(): string
    {
        return 'number_of_files_submitted';
    }
}
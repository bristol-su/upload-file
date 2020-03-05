<?php

namespace BristolSU\Module\UploadFile\CompletionConditions;

use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\Completion\Contracts\CompletionCondition;
use BristolSU\Support\ModuleInstance\Contracts\ModuleInstance;
use FormSchema\Schema\Form;

class NumberOfDocumentsWithStatus extends CompletionCondition
{

    public function isComplete($settings, ActivityInstance $activityInstance, ModuleInstance $moduleInstance): bool
    {
        return File::forResource($activityInstance->id, $moduleInstance->id())->get()->filter(function(File $file) use ($settings) {
            return $file->status === $settings['status'];
        })->count() >= $settings['number_of_files'];
    }

    public function percentage($settings, ActivityInstance $activityInstance, ModuleInstance $moduleInstance): int
    {
        $count = File::forResource($activityInstance->id, $moduleInstance->id())->get()->filter(function(File $file) use ($settings) {
            return $file->status === $settings['status'];
        })->count();
        $needed = ( $settings['number_of_files'] ?? 1);

        $percentage = (int) round(($count/$needed) * 100, 0);

        if($percentage > 100) {
            return 100; 
        }
        return $percentage;
    }


    public function options(): Form
    {
        return \FormSchema\Generator\Form::make()->withField(
            \FormSchema\Generator\Field::input('number_of_files')->inputType('number')->label('Number of Files')
                ->required(true)->default(1)->hint('The number of files that must be submitted')
                ->help('The number of documents that need to be submitted for the module to be marked as complete.')
        )->withField(
            \FormSchema\Generator\Field::input('status')->inputType('text')->label('Status')
                ->required(true)->default('Awaiting Approval')->hint('The status the files must be')
                ->help('The status of the files. The module will be complete when the number of files submitted have this status.')
        )->getSchema();
    }

    public function name(): string
    {
        return 'A number of documents with a given status have been submitted';
    }

    public function description(): string
    {
        return 'Complete when a given number of documents have been submitted and given a specific status';
    }

    public function alias(): string
    {
        return 'number_of_files_submitted_with_status';
    }
    
}
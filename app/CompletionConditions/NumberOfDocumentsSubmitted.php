<?php

namespace BristolSU\Module\UploadFile\CompletionConditions;

use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\Completion\Contracts\CompletionCondition;
use BristolSU\Support\ModuleInstance\Contracts\ModuleInstance;
use FormSchema\Schema\Form;

class NumberOfDocumentsSubmitted extends CompletionCondition
{

    public function isComplete($settings, ActivityInstance $activityInstance, ModuleInstance $moduleInstance): bool
    {
        return File::forResource($activityInstance->id, $moduleInstance->id())->count() >= $settings['number_of_files'];
    }

    public function percentage($settings, ActivityInstance $activityInstance, ModuleInstance $moduleInstance): int
    {
        $count = File::forResource($activityInstance->id, $moduleInstance->id)->count();
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
            \FormSchema\Generator\Field::number('number_of_files')->setLabel('Number of Files')
                ->setRequired(true)->setValue(1)->setHint('The number of files that must be submitted')
                ->setTooltip('The number of documents that need to be submitted for the module to be marked as complete.')
        )->getSchema();
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

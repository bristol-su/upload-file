<?php

namespace BristolSU\Module\Tests\UploadFile\CompletionConditions;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\CompletionConditions\NumberOfDocumentsSubmitted;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use FormSchema\Schema\Form;

class NumberOfDocumentsSubmittedTest extends TestCase
{

    /** @test */
    public function isComplete_returns_true_if_the_number_of_documents_submitted_is_greater_than_the_number_of_required_documents(){
        $moduleInstance = factory(ModuleInstance::class)->create();
        $activityInstance = factory(ActivityInstance::class)->create();

        $pageViews = factory(File::class, 5)->create([
            'module_instance_id' => $moduleInstance->id,
            'activity_instance_id' => $activityInstance->id
        ]);

        $condition = new NumberOfDocumentsSubmitted('uploadfile');
        $this->assertTrue(
            $condition->isComplete(['number_of_files' => 4], $activityInstance, $moduleInstance)
        );
    }

    /** @test */
    public function isComplete_returns_false_if_the_number_of_documents_submitted_is_less_than_the_number_of_required_documents(){
        $moduleInstance = factory(ModuleInstance::class)->create();
        $activityInstance = factory(ActivityInstance::class)->create();

        $pageViews = factory(File::class, 5)->create([
            'module_instance_id' => $moduleInstance->id,
            'activity_instance_id' => $activityInstance->id
        ]);

        $condition = new NumberOfDocumentsSubmitted('uploadfile');
        $this->assertFalse(
            $condition->isComplete(['number_of_files' => 6], $activityInstance, $moduleInstance)
        );
    }

    /** @test */
    public function isComplete_returns_true_if_the_number_of_documents_submitted_is_equal_to_the_number_of_required_documents(){
        $moduleInstance = factory(ModuleInstance::class)->create();
        $activityInstance = factory(ActivityInstance::class)->create();

        $pageViews = factory(File::class, 5)->create([
            'module_instance_id' => $moduleInstance->id,
            'activity_instance_id' => $activityInstance->id
        ]);

        $condition = new NumberOfDocumentsSubmitted('uploadfile');
        $this->assertTrue(
            $condition->isComplete(['number_of_files' => 5], $activityInstance, $moduleInstance)
        );
    }

    /** @test */
    public function percentage_returns_100_if_the_documents_submitted_are_equal_to_the_required_number(){
        $moduleInstance = factory(ModuleInstance::class)->create();
        $activityInstance = factory(ActivityInstance::class)->create();

        $pageViews = factory(File::class, 5)->create([
            'module_instance_id' => $moduleInstance->id,
            'activity_instance_id' => $activityInstance->id
        ]);

        $condition = new NumberOfDocumentsSubmitted('uploadfile');
        $this->assertEquals(100,
            $condition->percentage(['number_of_files' => 5], $activityInstance, $moduleInstance)
        );
    }

    /** @test */
    public function percentage_returns_100_if_the_documents_submitted_are_greater_than_the_required_number(){
        $moduleInstance = factory(ModuleInstance::class)->create();
        $activityInstance = factory(ActivityInstance::class)->create();

        $pageViews = factory(File::class, 10)->create([
            'module_instance_id' => $moduleInstance->id,
            'activity_instance_id' => $activityInstance->id
        ]);

        $condition = new NumberOfDocumentsSubmitted('uploadfile');
        $this->assertEquals(100,
            $condition->percentage(['number_of_files' => 5], $activityInstance, $moduleInstance)
        );
    }

    /** @test */
    public function percentage_returns_0_if_the_documents_submitted_are_zero(){
        $moduleInstance = factory(ModuleInstance::class)->create();
        $activityInstance = factory(ActivityInstance::class)->create();

        $condition = new NumberOfDocumentsSubmitted('uploadfile');
        $this->assertEquals(0,
            $condition->percentage(['number_of_files' => 5], $activityInstance, $moduleInstance)
        );
    }

    /** @test */
    public function percentage_returns_50_if_the_documents_submitted_are_half_the_required_required_documents(){
        $moduleInstance = factory(ModuleInstance::class)->create();
        $activityInstance = factory(ActivityInstance::class)->create();

        $pageViews = factory(File::class, 5)->create([
            'module_instance_id' => $moduleInstance->id,
            'activity_instance_id' => $activityInstance->id
        ]);

        $condition = new NumberOfDocumentsSubmitted('uploadfile');
        $this->assertEquals(50,
            $condition->percentage(['number_of_files' => 10], $activityInstance, $moduleInstance)
        );
    }

    /** @test */
    public function percentage_returns_75_if_the_documents_submitted_are_three_quarters_the_required_required_documents(){
        $moduleInstance = factory(ModuleInstance::class)->create();
        $activityInstance = factory(ActivityInstance::class)->create();

        $pageViews = factory(File::class, 3)->create([
            'module_instance_id' => $moduleInstance->id,
            'activity_instance_id' => $activityInstance->id
        ]);

        $condition = new NumberOfDocumentsSubmitted('uploadfile');
        $this->assertEquals(75,
            $condition->percentage(['number_of_files' => 4], $activityInstance, $moduleInstance)
        );
    }

    /** @test */
    public function the_alias_is_given(){
        $this->assertEquals('number_of_files_submitted', (new NumberOfDocumentsSubmitted('uploadfile'))->alias());
    }

    /** @test */
    public function name_returns_a_string(){
        $this->assertIsString((new NumberOfDocumentsSubmitted('uploadfile'))->name());
    }

    /** @test */
    public function description_returns_a_string(){
        $this->assertIsString((new NumberOfDocumentsSubmitted('uploadfile'))->description());
    }

    /** @test */
    public function options_returns_a_form_schema(){
        $this->assertInstanceOf(Form::class, (new NumberOfDocumentsSubmitted('uploadfile'))->options());
    }
    
}
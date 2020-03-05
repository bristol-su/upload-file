<?php

namespace BristolSU\Module\Tests\UploadFile\CompletionConditions;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\CompletionConditions\NumberOfDocumentsWithStatus;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Module\UploadFile\Models\FileStatus;
use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use FormSchema\Schema\Form;

class NumberOfDocumentsWithStatusTest extends TestCase
{
    
    /** @test */
    public function isComplete_returns_true_if_the_number_of_documents_of_the_correct_status_is_greater_than_the_required_documents(){
        $file1 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file2 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file3 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        factory(FileStatus::class)->create(['file_id' => $file1->id, 'status' => 'Approved']);
        factory(FileStatus::class)->create(['file_id' => $file2->id, 'status' => 'Approved']);
        factory(FileStatus::class)->create(['file_id' => $file3->id, 'status' => 'Approved']);

        $condition = new NumberOfDocumentsWithStatus('uploadfile');
        $this->assertTrue(
            $condition->isComplete(['number_of_files' => 2, 'status' => 'Approved'], $this->getActivityInstance(), $this->getModuleInstance())
        );
    }

    /** @test */
    public function isComplete_returns_true_if_the_number_of_documents_of_the_correct_status_is_equal_to_the_required_documents(){
        $file1 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file2 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file3 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        factory(FileStatus::class)->create(['file_id' => $file1->id, 'status' => 'Rejected']);
        factory(FileStatus::class)->create(['file_id' => $file2->id, 'status' => 'Rejected']);
        factory(FileStatus::class)->create(['file_id' => $file3->id, 'status' => 'Rejected']);

        $condition = new NumberOfDocumentsWithStatus('uploadfile');
        $this->assertTrue(
            $condition->isComplete(['number_of_files' => 3, 'status' => 'Rejected'], $this->getActivityInstance(), $this->getModuleInstance())
        );
    }

    /** @test */
    public function isComplete_returns_false_if_the_number_of_documents_of_the_correct_status_is_less_than_the_required_documents(){
        $file1 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file2 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file3 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        factory(FileStatus::class)->create(['file_id' => $file1->id, 'status' => 'Rejected']);
        factory(FileStatus::class)->create(['file_id' => $file2->id, 'status' => 'Rejected']);
        factory(FileStatus::class)->create(['file_id' => $file3->id, 'status' => 'Rejected']);

        $condition = new NumberOfDocumentsWithStatus('uploadfile');
        $this->assertFalse(
            $condition->isComplete(['number_of_files' => 4, 'status' => 'Rejected'], $this->getActivityInstance(), $this->getModuleInstance())
        );
    }
    
    /** @test */
    public function isComplete_distinguishes_between_statuses(){
        $file1 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file2 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file3 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        factory(FileStatus::class)->create(['file_id' => $file1->id, 'status' => 'Rejected']);
        factory(FileStatus::class)->create(['file_id' => $file2->id, 'status' => 'Rejected']);
        factory(FileStatus::class)->create(['file_id' => $file3->id, 'status' => 'Approved']);

        $condition = new NumberOfDocumentsWithStatus('uploadfile');
        $this->assertFalse(
            $condition->isComplete(['number_of_files' => 3, 'status' => 'Rejected'], $this->getActivityInstance(), $this->getModuleInstance())
        );
    }
    
    /** @test */
    public function percentage_returns_100_if_the_documents_submitted_are_equal_to_the_required_number(){
        $file1 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file2 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file3 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        factory(FileStatus::class)->create(['file_id' => $file1->id, 'status' => 'Approved']);
        factory(FileStatus::class)->create(['file_id' => $file2->id, 'status' => 'Approved']);
        factory(FileStatus::class)->create(['file_id' => $file3->id, 'status' => 'Approved']);

        $condition = new NumberOfDocumentsWithStatus('uploadfile');
        $this->assertEquals(100,
            $condition->percentage(['number_of_files' => 3, 'status' => 'Approved'], $this->getActivityInstance(), $this->getModuleInstance())
        );
    }

    /** @test */
    public function percentage_returns_100_if_the_documents_submitted_are_greater_than_the_required_number(){
        $file1 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file2 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file3 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        factory(FileStatus::class)->create(['file_id' => $file1->id, 'status' => 'Approved']);
        factory(FileStatus::class)->create(['file_id' => $file2->id, 'status' => 'Approved']);
        factory(FileStatus::class)->create(['file_id' => $file3->id, 'status' => 'Approved']);

        $condition = new NumberOfDocumentsWithStatus('uploadfile');
        $this->assertEquals(100,
            $condition->percentage(['number_of_files' => 2, 'status' => 'Approved'], $this->getActivityInstance(), $this->getModuleInstance())
        );
    }

    /** @test */
    public function percentage_returns_75_if_the_documents_submitted_are_less_than_the_required_number_by_3_of_4(){
        $file1 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file2 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file3 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        $file4 = factory(File::class)->create(['module_instance_id' => $this->getModuleInstance()->id(), 'activity_instance_id' => $this->getActivityInstance()->id]);
        factory(FileStatus::class)->create(['file_id' => $file1->id, 'status' => 'Approved']);
        factory(FileStatus::class)->create(['file_id' => $file2->id, 'status' => 'Approved']);
        factory(FileStatus::class)->create(['file_id' => $file3->id, 'status' => 'Rejected']);
        factory(FileStatus::class)->create(['file_id' => $file4->id, 'status' => 'Approved']);

        $condition = new NumberOfDocumentsWithStatus('uploadfile');
        $this->assertEquals(75,
            $condition->percentage(['number_of_files' => 4, 'status' => 'Approved'], $this->getActivityInstance(), $this->getModuleInstance())
        );
    }

    /** @test */
    public function percentage_returns_0_if_the_documents_submitted_are_zero(){
        $condition = new NumberOfDocumentsWithStatus('uploadfile');
        $this->assertEquals(0,
            $condition->percentage(['number_of_files' => 4, 'status' => 'Approved'], $this->getActivityInstance(), $this->getModuleInstance())
        );
    }

    /** @test */
    public function the_alias_is_given(){
        $this->assertEquals('number_of_files_submitted_with_status', (new NumberOfDocumentsWithStatus('uploadfile'))->alias());
    }

    /** @test */
    public function name_returns_a_string(){
        $this->assertIsString((new NumberOfDocumentsWithStatus('uploadfile'))->name());
    }

    /** @test */
    public function description_returns_a_string(){
        $this->assertIsString((new NumberOfDocumentsWithStatus('uploadfile'))->description());
    }

    /** @test */
    public function options_returns_a_form_schema(){
        $this->assertInstanceOf(Form::class, (new NumberOfDocumentsWithStatus('uploadfile'))->options());
    }
    
}
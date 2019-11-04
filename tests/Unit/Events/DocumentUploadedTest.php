<?php

namespace BristolSU\Module\Tests\UploadFile\Unit\Events;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Models\File;

class DocumentUploadedTest extends TestCase
{

    /** @test */
    public function it_returns_a_set_of_usable_fields(){
        $file = factory(File::class)->create(['uploaded_by' => $this->user->id, 'resource_id' => $this->user->id]);
        
        $event = new DocumentUploaded($file);
        
        $this->assertEquals([
            'title' => $file->title,
            'filename' => $file->filename
        ], $event->getFields());
    }
    
    /** @test */
    public function it_returns_metadata_for_the_fields(){
        $this->assertArrayHasKey('title', DocumentUploaded::getFieldMetaData());
        $this->assertArrayHasKey('filename', DocumentUploaded::getFieldMetaData());
        $this->assertArrayHasKey('label', DocumentUploaded::getFieldMetaData()['title']);
        $this->assertArrayHasKey('helptext', DocumentUploaded::getFieldMetaData()['title']);
        $this->assertArrayHasKey('label', DocumentUploaded::getFieldMetaData()['filename']);
        $this->assertArrayHasKey('helptext', DocumentUploaded::getFieldMetaData()['filename']);
    }
    
}
<?php

namespace BristolSU\Module\UploadFile\Events;

use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Action\Contracts\TriggerableEvent;

class DocumentUploaded implements TriggerableEvent
{

    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function getFields(): array
    {
        return [
            'title' => $this->file->title,
            'filename' => $this->file->filename
        ];
    }

    public static function getFieldMetaData(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'helptext' => 'The title given to the document when uploaded',
            ],
            'filename' => [
                'label' => 'Filename',
                'helptext' => 'The original filename of the document'
            ]  
        ];
    }
}
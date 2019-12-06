<?php


namespace BristolSU\Module\UploadFile\Events;


use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Module\UploadFile\Models\FileStatus;
use BristolSU\Support\Action\Contracts\TriggerableEvent;

class StatusChanged implements TriggerableEvent
{

    /**
     * @var File
     */
    private $file;
    /**
     * @var FileStatus
     */
    private $fileStatus;

    public function __construct(File $file, FileStatus $fileStatus)
    {
        $this->file = $file;
        $this->fileStatus = $fileStatus;
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return [
            'file_title' => $this->file->title,
            'new_status' => $this->fileStatus->status
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getFieldMetaData(): array
    {
        return [
            'file_title' => [
                'label' => 'Title',
                'helptext' => 'The title given to the document when uploaded',
            ],
            'new_status' => [
                'label' => 'Status',
                'helptext' => 'The new status of the document'
            ]
        ];
    }
}
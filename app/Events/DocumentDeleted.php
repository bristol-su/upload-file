<?php

namespace BristolSU\Module\UploadFile\Events;

use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Action\Contracts\TriggerableEvent;

class DocumentDeleted implements TriggerableEvent
{

    /**
     * @var File
     */
    public $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return [
            'file_id' => $this->file->id,
            'title' => $this->file->title,
            'description' => $this->file->description,
            'filename' => $this->file->filename,
            'mime' => $this->file->mime,
            'size' => $this->file->size,
            'uploaded_by_id' => $this->file->uploaded_by->id(),
            'uploaded_by_email' => $this->file->uploaded_by->data()->email(),
            'uploaded_by_first_name' => $this->file->uploaded_by->data()->firstName(),
            'uploaded_by_last_name' => $this->file->uploaded_by->data()->lastName(),
            'uploaded_by_preferred_name' => $this->file->uploaded_by->data()->preferredName(),
            'module_instance_id' => $this->file->module_instance_id,
            'activity_instance_id' => $this->file->activity_instance_id,
            'uploaded_at' => $this->file->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->file->updated_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->file->{$this->file->getDeletedAtColumn()}->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getFieldMetaData(): array
    {
        return [
            'file_id' => [
                'label' => 'File ID',
                'helptext' => 'The ID of the file'
            ],
            'title' => [
                'label' => 'Title',
                'helptext' => 'The title given to the document when uploaded',
            ],
            'description' => [
                'label' => 'Description',
                'helptext' => 'A description of the file'
            ],
            'filename' => [
                'label' => 'Filename',
                'helptext' => 'The original filename of the document'
            ],
            'mime' => [
                'label' => 'Mimetype',
                'helptext' => 'The mime type of the file'
            ],
            'size' => [
                'label' => 'File Size',
                'helptext' => 'The size of the file in bytes'
            ],
            'uploaded_by_id' => [
                'label' => 'User ID',
                'helptext' => 'ID of the user who uploaded the file'
            ],
            'uploaded_by_email' => [
                'label' => 'User Email',
                'helptext' => 'Email of the user who uploaded the file'
            ],
            'uploaded_by_first_name' => [
                'label' => 'User First Name',
                'helptext' => 'First Name of the user who uploaded the file'
            ],
            'uploaded_by_last_name' => [
                'label' => 'User Last Name',
                'helptext' => 'Last Name of the user who uploaded the file'
            ],
            'uploaded_by_preferred_name' => [
                'label' => 'User Preferred Name',
                'helptext' => 'Preferred Name of the user who uploaded the file'
            ],
            'module_instance_id' => [
                'label' => 'Module Instance ID',
                'helptext' => 'ID of the module instance the file was uploaded to'
            ],
            'activity_instance_id' => [
                'label' => 'Activity Instance ID',
                'helptext' => 'ID of the activity instance that uploaded the file'
            ],
            'uploaded_at' => [
                'label' => 'Uploaded At',
                'helptext' => 'Time and date the file was uploaded at'
            ],
            'updated_at' => [
                'label' => 'Updated At',
                'helptext' => 'Time and date the file was last updated'
            ],
            'deleted_at' => [
                'label' => 'Deleted At',
                'helptext' => 'Time and date the file was deleted'
            ]
        ];
    }
}
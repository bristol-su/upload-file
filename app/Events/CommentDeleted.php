<?php

namespace BristolSU\Module\UploadFile\Events;

use BristolSU\Module\UploadFile\Models\Comment;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Action\Contracts\TriggerableEvent;

class CommentDeleted implements TriggerableEvent
{

    /**
     * @var Comment
     */
    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return [
            'file_id' => $this->comment->file->id,
            'file_title' => $this->comment->file->title,
            'file_description' => $this->comment->file->description,
            'file_filename' => $this->comment->file->filename,
            'file_mime' => $this->comment->file->mime,
            'file_size' => $this->comment->file->size,
            'file_uploaded_by_id' => $this->comment->file->uploaded_by->id(),
            'file_uploaded_by_email' => $this->comment->file->uploaded_by->data()->email(),
            'file_uploaded_by_first_name' => $this->comment->file->uploaded_by->data()->firstName(),
            'file_uploaded_by_last_name' => $this->comment->file->uploaded_by->data()->lastName(),
            'file_uploaded_by_preferred_name' => $this->comment->file->uploaded_by->data()->preferredName(),
            'file_module_instance_id' => $this->comment->file->module_instance_id,
            'file_activity_instance_id' => $this->comment->file->activity_instance_id,
            'file_uploaded_at' => $this->comment->file->created_at->format('Y-m-d H:i:s'),
            'file_updated_at' => $this->comment->file->updated_at->format('Y-m-d H:i:s'),
            'comment' => $this->comment->comment,
            'comment_id' => $this->comment->id,
            'comment_posted_at' => $this->comment->created_at->format('Y-m-d H:i:s'),
            'comment_edited_at' => $this->comment->updated_at->format('Y-m-d H:i:s'),
            'comment_posted_by_id' => $this->comment->posted_by->id(),
            'comment_posted_by_email' => $this->comment->posted_by->data()->email(),
            'comment_posted_by_first_name' => $this->comment->posted_by->data()->firstName(),
            'comment_posted_by_last_name' => $this->comment->posted_by->data()->lastName(),
            'comment_posted_by_preferred_name' => $this->comment->posted_by->data()->preferredName(),
            'comment_deleted_at' => $this->comment->deleted_at->format('Y-m-d H:i:s')
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
            'file_title' => [
                'label' => 'File Title',
                'helptext' => 'The title given to the document when uploaded',
            ],
            'file_description' => [
                'label' => 'File Description',
                'helptext' => 'A description of the file'
            ],
            'file_filename' => [
                'label' => 'Filename',
                'helptext' => 'The original filename of the document'
            ],
            'file_mime' => [
                'label' => 'File Mimetype',
                'helptext' => 'The mime type of the file'
            ],
            'file_size' => [
                'label' => 'File Size',
                'helptext' => 'The size of the file in bytes'
            ],
            'file_uploaded_by_id' => [
                'label' => 'File Uploader User ID',
                'helptext' => 'ID of the user who uploaded the file'
            ],
            'file_uploaded_by_email' => [
                'label' => 'File Uploader User Email',
                'helptext' => 'Email of the user who uploaded the file'
            ],
            'file_uploaded_by_first_name' => [
                'label' => 'File Uploader User First Name',
                'helptext' => 'First Name of the user who uploaded the file'
            ],
            'file_uploaded_by_last_name' => [
                'label' => 'File Uploader User Last Name',
                'helptext' => 'Last Name of the user who uploaded the file'
            ],
            'file_uploaded_by_preferred_name' => [
                'label' => 'File Uploader User Preferred Name',
                'helptext' => 'Preferred Name of the user who uploaded the file'
            ],
            'file_module_instance_id' => [
                'label' => 'Module Instance ID',
                'helptext' => 'ID of the module instance the file was uploaded to'
            ],
            'file_activity_instance_id' => [
                'label' => 'Activity Instance ID',
                'helptext' => 'ID of the activity instance that uploaded the file'
            ],
            'file_uploaded_at' => [
                'label' => 'File Uploaded At',
                'helptext' => 'Time and date the file was uploaded at'
            ],
            'file_updated_at' => [
                'label' => 'File Updated At',
                'helptext' => 'Time and date the file was last updated'
            ],
            'comment' => [
                'label' => 'Comment Text',
                'helptext' => 'The text that makes up the comment'
            ],
            'comment_id' => [
                'label' => 'Comment ID',
                'helptext' => 'The ID of the comment'
            ],
            'comment_posted_at' => [
                'label' => 'Comment Posted At',
                'helptext' => 'The date and time at which the comment was posted'
            ],
            'comment_edited_at' => [
                'label' => 'Comment Edited At',
                'helptext' => 'The date and time at which the comment was last edited'
            ],
            'comment_posted_by_id' => [
                'label' => 'Comment Posted By',
                'helptext' => 'ID of the user who posted the comment'
            ],
            'comment_posted_by_email' => [
                'label' => 'Comment Posted By Email',
                'helptext' => 'Email of the user who posted the comment'
            ],
            'comment_posted_by_first_name' => [
                'label' => 'Comment Posted By First Name',
                'helptext' => 'First Name of the user who posted the comment'
            ],
            'comment_posted_by_last_name' => [
                'label' => 'Comment Posted By Last Name',
                'helptext' => 'Last Name of the user who posted the comment'
            ],
            'comment_posted_by_preferred_name' => [
                'label' => 'Comment Posted By Preferred Name',
                'helptext' => 'Preferred Name of the user who posted the comment'
            ],
            'comment_deleted_at' => [
                'label' => 'Comment Deleted At',
                'helptext' => 'The date and time at which the comment was deleted'
            ]
        ];
    }
}
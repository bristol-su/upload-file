<?php

namespace BristolSU\Module\UploadFile;

use BristolSU\Module\UploadFile\CompletionConditions\NumberOfDocumentsSubmitted;
use BristolSU\Module\UploadFile\CompletionConditions\NumberOfDocumentsWithStatus;
use BristolSU\Module\UploadFile\Events\CommentCreated;
use BristolSU\Module\UploadFile\Events\CommentDeleted;
use BristolSU\Module\UploadFile\Events\CommentUpdated;
use BristolSU\Module\UploadFile\Events\DocumentDeleted;
use BristolSU\Module\UploadFile\Events\DocumentUpdated;
use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Events\StatusChanged;
use BristolSU\Module\UploadFile\Fields\TagList;
use BristolSU\Module\UploadFile\Models\Comment;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Module\UploadFile\Models\FileStatus;
use BristolSU\Support\ActivityInstance\Contracts\ActivityInstanceResolver;
use BristolSU\Support\Completion\Contracts\CompletionConditionManager;
use BristolSU\Support\Module\ModuleServiceProvider as ServiceProvider;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Faker\Generator;
use FormSchema\Generator\Field;
use FormSchema\Generator\Form as FormGenerator;
use FormSchema\Generator\Group;
use FormSchema\Schema\Form;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;

class ModuleServiceProvider extends ServiceProvider
{

    protected $permissions = [
        // ##### Web #####
        'view-page' => [
            'name' => 'View Participant Page',
            'description' => 'View the main page of the module.',
            'admin' => false
        ],
        'admin.view-page' => [
            'name' => 'View Admin Page',
            'description' => 'View the administrator page of the module.',
            'admin' => true
        ],

        // ##### Api #####
        // Files
        'file.store' => [
            'name' => 'Upload a new file',
            'description' => 'Allow the ability to upload a file.',
            'admin' => false
        ],
        'file.download' => [
            'name' => 'Download a file',
            'description' => 'Allow the user to download a file',
            'admin' => false
        ],
        'file.index' => [
            'name' => 'See files',
            'description' => 'Allow the user to view files',
            'admin' => false
        ],
        'file.destroy' => [
            'name' => 'Delete files',
            'description' => 'Allow the user to delete files',
            'admin' => false
        ],
        'file.update' => [
            'name' => 'Edit files',
            'description' => 'Allow the user to edit files',
            'admin' => false
        ],
        // Comments
        'comment.index' => [
            'name' => 'See comments',
            'description' => 'Allow the user to see comments',
            'admin' => false
        ],
        'comment.store' => [
            'name' => 'Comment',
            'description' => 'Allow the user to comment in files',
            'admin' => false
        ],
        'comment.destroy' => [
            'name' => 'Delete a Comment',
            'description' => 'Allow the user to delete a comment.',
            'admin' => false
        ],
        'comment.update' => [
            'name' => 'Update a Comment',
            'description' => 'Allow the user to update a comment.',
            'admin' => false
        ],

        // Files
        'admin.file.index' => [
            'name' => 'View all files',
            'description' => 'Allow the user to view all uploaded files',
            'admin' => true
        ],
        'admin.file.store' => [
            'name' => 'Upload a new file',
            'description' => 'Allow the ability to upload a file on behalf of a user.',
            'admin' => true
        ],
        'admin.file.destroy' => [
            'name' => 'Delete a file',
            'description' => 'Allow the user to delete any uploaded file',
            'admin' => true
        ],
        'admin.file.update' => [
            'name' => 'Update a File',
            'description' => 'Allow the user to update any file name/description or reassign the file.',
            'admin' => true
        ],
        'admin.file.download' => [
            'name' => 'Download files',
            'description' => 'Allow the user to download any uploaded files',
            'admin' => true
        ],
        // Statuses
        'admin.status.create' => [
            'name' => 'Change document status',
            'description' => 'Allow the user to change the status of any file',
            'admin' => true
        ],
        // Comments
        'admin.comment.index' => [
            'name' => 'See comments',
            'description' => 'Allow the admin to see comments',
            'admin' => true
        ],
        'admin.comment.store' => [
            'name' => 'Comment',
            'description' => 'Allow the admin to comment in files',
            'admin' => true
        ],
        'admin.comment.destroy' => [
            'name' => 'Delete a Comment',
            'description' => 'Allow the admin to delete a comment.',
            'admin' => true
        ],
        'admin.comment.update' => [
            'name' => 'Update a Comment',
            'description' => 'Allow the admin to update a comment.',
            'admin' => true
        ],
    ];

    protected $events = [
        CommentCreated::class => [
            'name' => 'Comment Left',
            'description' => 'When a comment has been left'
        ],
        CommentDeleted::class => [
            'name' => 'Comment Deleted',
            'description' => 'When a comment has been deleted'
        ],
        CommentUpdated::class => [
            'name' => 'Comment Updated',
            'description' => 'When a comment has been updated'
        ],
        DocumentDeleted::class => [
            'name' => 'Document Deleted',
            'description' => 'When a document is deleted'
        ],
        DocumentUpdated::class => [
            'name' => 'Document Updated',
            'description' => 'When a document is updated'
        ],
        DocumentUploaded::class => [
            'name' => 'Document Uploaded',
            'description' => 'When a document is uploaded'
        ],
        StatusChanged::class => [
            'name' => 'Status Changed',
            'description' => 'When the status of a document is changed'
        ]
    ];

    protected $commands = [

    ];

    public function namespace()
    {
        return null;
    }

    public function baseDirectory()
    {
        return __DIR__ . '/..';
    }

    public function register()
    {
        parent::register(); // TODO: Change the autogenerated stub
    }

    public function boot()
    {
        parent::boot();

        if (class_exists(\Faker\Factory::class) && !app()->environment('production')) {
            $this->app->make(Factory::class)->load($this->baseDirectory() . '/database/factories');
        }

        $this->app->make(CompletionConditionManager::class)->register(
            $this->alias(), 'number_of_files_submitted', NumberOfDocumentsSubmitted::class
        );
        $this->app->make(CompletionConditionManager::class)->register(
            $this->alias(), 'number_of_files_submitted_with_status', NumberOfDocumentsWithStatus::class
        );

        Route::bind('uploadfile_file', function ($id) {
            $file = File::findOrFail($id);
            if (request()->route('module_instance_slug') && (int)$file->module_instance_id === request()->route('module_instance_slug')->id()) {
                return $file;
            }
            throw (new ModelNotFoundException)->setModel(File::class);
        });

        Route::bind('uploadfile_old_file', function ($id) {
            $file = File::findOrFail($id);

            $currentActivityInstance = app(ActivityInstanceResolver::class)->getActivityInstance();
            $fileActivityInstance = $file->activityInstance();
            if ($currentActivityInstance->resource_type === $fileActivityInstance->resource_type
                && (int)$currentActivityInstance->resource_id === (int)$fileActivityInstance->resource_id) {
                return $file;
            }
            throw (new ModelNotFoundException)->setModel(File::class);
        });

        Route::bind('uploadfile_file_status', function ($id) {
            $fileStatus = FileStatus::findOrFail($id);
            if (request()->route('module_instance_slug') && (int)$fileStatus->file->module_instance_id === request()->route('module_instance_slug')->id()) {
                return $fileStatus;
            }
            throw (new ModelNotFoundException)->setModel(FileStatus::class);
        });

        Route::bind('uploadfile_comment', function ($id) {
            $comment = Comment::findOrFail($id);
            if (request()->route('module_instance_slug') && (int)$comment->file->module_instance_id === request()->route('module_instance_slug')->id()) {
                return $comment;
            }
            throw (new ModelNotFoundException)->setModel(Comment::class);
        });
    }

    public function alias(): string
    {
        return 'uploadfile';
    }

    public function settings(): Form
    {

        return FormGenerator::make()->withGroup(
            Group::make('Page Design')->withField(
                Field::textInput('title')
                    ->setLabel('Module Title')
                    ->setValue('Page Title')
                    ->setHint('This will appear at the top of the page')
            )->withField(
                Field::textArea('description')->setLabel('Description')->setHint('This will appear at the top of the page')->setValue('Description')
            )
        )->withGroup(
            Group::make('New Documents')->withField(
                Field::textInput('document_title')->setLabel('Default document title')->setHint('This will be the setValue title of a new file')->setValue('Document')
            )->withField(
                Field::switch('multiple_files')->setLabel('Multiple Files')->setHint('Should multiple files be able to be uploaded at the same time?')
                    ->setOnText('Allow')->setOffText('Do not allow')->setValue(true)
            )->withField(
                Field::checkList('allowed_extensions')->setLabel('Allowed file types')->setHint('Which file types can be uploaded?')
                    ->setOptions(
                        collect($this->app['config']->get($this->alias() . '.file_types'))->map(fn($item) => ['id' => $item['value'], 'text' => $item['name']])->toArray()
                    )
                    ->setValue(['doc', 'docx', 'odt', 'rtf', 'txt', 'csv', 'ppt', 'pptx', 'pdf', 'xls'])
            )
        )->withGroup(
            Group::make('Status Changes')->withField(
                Field::select('initial_status')->setLabel('Initial Status')->setHint('What status should all new files have?')
                    ->setValue('Awaiting Approval')->setSelectOptions(
                        collect($this->app['config']->get($this->alias() . '.statuses'))->map(fn($item) => ['id' => $item, 'value' => $item])->toArray()
                    )
            )->withField(
                Field::select('statuses')->setLabel('Available Statuses')->setHint('A list of available statuses')
                    ->setValue(['Awaiting Approval'])->setSelectOptions(
                        collect($this->app['config']->get($this->alias() . '.statuses'))->map(fn($item) => ['id' => $item, 'value' => $item])->toArray()
                    )
                    ->setMultiple(true)
            )
        )->withGroup(
            Group::make('Tags')->withField(
                Field::tags('new_tags')->setLabel('New Tags')->setHint('What tags should we assign when a new document is uploaded?')
            )->withField(
                Field::tags('tags_to_merge')->setLabel('Tags to merge')->setHint('Any files with these tags will also be shown to the user')
            )
        )->getSchema();
    }
}

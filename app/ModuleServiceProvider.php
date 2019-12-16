<?php

namespace BristolSU\Module\UploadFile;

use BristolSU\Module\UploadFile\CompletionConditions\NumberOfDocumentsSubmitted;
use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Completion\Contracts\CompletionConditionManager;
use BristolSU\Support\Module\ModuleServiceProvider as ServiceProvider;
use FormSchema\Generator\Field;
use FormSchema\Generator\Form as FormGenerator;
use FormSchema\Generator\Group;
use FormSchema\Schema\Form;
use Illuminate\Support\Facades\Route;

class ModuleServiceProvider extends ServiceProvider
{

    protected $permissions = [
        'view-page' => [
            'name' => 'View Participant Page',
            'description' => 'View the main page of the module.',
            'admin' => false
        ],
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
        'admin.view-page' => [
            'name' => 'View Admin Page',
            'description' => 'View the administrator page of the module.',
            'admin' => true
        ],
        'admin.file.index' => [
            'name' => 'View all files',
            'description' => 'Allow the user to view all uploaded files',
            'admin' => true
        ],
        'admin.file.download' => [
            'name' => 'Download files',
            'description' => 'Allow the user to download any uploaded files',
            'admin' => true
        ],
        'admin.status.create' => [
            'name' => 'Change document status',
            'description' => 'Allow the user to change the status of any file',
            'admin' => true
        ]
    ];

    protected $events = [
        DocumentUploaded::class => [
            'name' => 'Document Uploaded',
            'description' => 'When a document is uploaded'
        ]
    ];
    
    protected $commands = [
        
    ];
    
    public function alias(): string
    {
        return 'uploadfile';
    }

    public function namespace()
    {
        return '\BristolSU\Module\UploadFile\Http\Controllers';
    }
    
    public function baseDirectory()
    {
        return __DIR__ . '/..';
    }

    public function boot()
    {
        parent::boot();
        
        // TODO Move into module service provider
        $this->app->make(CompletionConditionManager::class)->register(
            $this->alias(), 'number_of_files_submitted', NumberOfDocumentsSubmitted::class
        );
        
        Route::bind($this->alias() . '_file', function($id) {
            return File::findOrFail($id);
        });
    }

    public function settings(): Form
    {

        return FormGenerator::make()->withGroup(
            Group::make('Page Design')->withField(
                Field::input('title')->inputType('text')->label('Module Title')->default('Page Title')
            )->withField(
                Field::textArea('description')->label('Description')->hint('This will appear at the top of the page')->rows(4)->default('Description')
            )
        )->withGroup(
            Group::make('New Documents')->withField(
                Field::input('document_title')->inputType('text')->label('Default document title')->hint('This will be the default title of a new file')->default('Document')
            )->withField(
                Field::switch('multiple_files')->label('Multiple Files')->hint('Should multiple files be able to be uploaded at the same time?')
                    ->textOn('Allow')->textOff('Do not allow')->default(true)
            )->withField(
                Field::checkList('allowed_extensions')->label('Allowed file types')->hint('Which file types can be uploaded?')
                    ->listBox(true)->values($this->app['config']->get($this->alias() . '.file_types'))
		    ->default(['doc', 'docx', 'odt', 'rtf', 'txt', 'csv', 'ppt', 'pptx', 'pdf', 'xls'])
            )
        )->withGroup(
            Group::make('Status Changes')->withField(
                Field::input('initial_status')->inputType('select')->label('Initial Status')->hint('What status should all new files have?')
                        ->default('Awaiting Approval')->values($this->app['config']->get($this->alias() . '.statuses'))
            )->withField(
                Field::checkList('statuses')->label('Available Statuses')->hint('A list of available statuses')
                    ->listBox(true)->values($this->app['config']->get($this->alias() . '.statuses'))
            )
        )->getSchema();
    }
}

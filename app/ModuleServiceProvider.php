<?php

namespace BristolSU\Module\UploadFile;

use BristolSU\Module\UploadFile\CompletionConditions\NumberOfDocumentsSubmitted;
use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Completion\Contracts\CompletionConditionManager;
use BristolSU\Support\Module\ModuleServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class ModuleServiceProvider extends ServiceProvider
{

    protected $permissions = [
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
    
}

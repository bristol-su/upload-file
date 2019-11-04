<?php

namespace BristolSU\Module\UploadFile;

use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\EventStore\Contracts\StorableEvent;
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

    public function baseDirectory()
    {
        return __DIR__ . '/..';
    }

    public function boot()
    {
        parent::boot();
        
        Route::bind('uploadfile_file', function($id) {
            return File::findOrFail($id);
        });
    }
    
}

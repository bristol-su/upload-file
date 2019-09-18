<?php

namespace BristolSU\Module\UploadFile;

use BristolSU\Support\EventStore\Contracts\StorableEvent;
use BristolSU\Support\Module\ModuleServiceProvider as ServiceProvider;

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
        ]
    ];

    protected $completionEvents = [
        StorableEvent::class => [
            'name' => 'On Event Fired',
            'description' => 'This event will be fired when...'
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
    
}

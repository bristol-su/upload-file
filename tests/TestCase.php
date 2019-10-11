<?php

namespace BristolSU\Module\Tests\UploadFile;

use BristolSU\Module\UploadFile\ModuleServiceProvider;
use BristolSU\Support\Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function alias(): string
    {
        return 'uploadfile';
    }

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            ModuleServiceProvider::class
        ]);
    }
    
}
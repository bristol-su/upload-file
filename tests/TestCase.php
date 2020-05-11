<?php

namespace BristolSU\Module\Tests\UploadFile;

use BristolSU\Module\UploadFile\ModuleServiceProvider;
use BristolSU\Support\Testing\AssertsEloquentModels;
use BristolSU\Support\Testing\CreatesModuleEnvironment;
use BristolSU\Support\Testing\TestCase as BaseTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

abstract class TestCase extends BaseTestCase
{
    use CreatesModuleEnvironment, AssertsEloquentModels, ProphecyTrait;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->createModuleEnvironment('uploadfile');
    }

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            ModuleServiceProvider::class
        ]);
    }
    
}
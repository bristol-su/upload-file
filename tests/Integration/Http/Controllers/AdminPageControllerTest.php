<?php

namespace BristolSU\Module\Tests\UploadFile\Integration\Http\Controllers;

use BristolSU\Module\Tests\UploadFile\TestCase;

class AdminPageControllerTest extends TestCase
{

    /** @test */
    public function index_authorizes_against_ability() {
        $this->assertRequiresAuthorization('get', $this->adminUrl('/'),  'admin.view-page');
    }

    /** @test */
    public function index_returns_a_view()
    {
        $this->bypassAuthorization();
        $response = $this->get($this->adminUrl());
        $response->assertViewIs('uploadfile::admin');
    }
    
}
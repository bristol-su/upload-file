<?php

namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\Admin;

use BristolSU\Module\Tests\UploadFile\TestCase;

class AdminPageControllerTest extends TestCase
{

    /** @test */
    public function index_returns_403_if_ability_not_owned() {
        $this->revokePermissionTo('uploadfile.admin.view-page');
        
        $response = $this->get($this->adminUrl('/'));
        $response->assertStatus(403);
    }

    /** @test */
    public function index_returns_200_if_ability_owned() {
        $this->givePermissionTo('uploadfile.admin.view-page');

        $response = $this->get($this->adminUrl('/'));
        $response->assertStatus(200);
    }

    /** @test */
    public function index_returns_a_view()
    {
        $this->bypassAuthorization();
        
        $response = $this->get($this->adminUrl());
        $response->assertViewIs('uploadfile::admin');
    }
    
}
<?php

namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\Participant;

use BristolSU\Module\Tests\UploadFile\TestCase;

class ParticipantPageControllerTest extends TestCase
{

    /** @test */
    public function index_returns_403_if_ability_not_owned() {
        $this->revokePermissionTo('uploadfile.view-page');
        
        $response = $this->get($this->userUrl('/'));
        $response->assertStatus(403);
    }

    /** @test */
    public function index_returns_200_if_ability_owned() {
        $this->givePermissionTo('uploadfile.view-page');

        $response = $this->get($this->userUrl('/'));
        $response->assertStatus(200);
    }

    /** @test */
    public function index_returns_a_view()
    {
        $this->bypassAuthorization();
        
        $response = $this->get($this->userUrl());
        $response->assertViewIs('uploadfile::participant');
    }
    
}
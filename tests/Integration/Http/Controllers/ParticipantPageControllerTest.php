<?php

namespace BristolSU\Module\Tests\UploadFile\Integration\Http\Controllers;

use BristolSU\Module\Tests\UploadFile\TestCase;

class ParticipantPageControllerTest extends TestCase
{

    /** @test */
    public function index_authorizes_against_ability() {
        $this->assertRequiresAuthorization('get', $this->userUrl('/'),  'view-page');
    }

    /** @test */
    public function index_returns_a_view()
    {
        $this->bypassAuthorization();
        $response = $this->get($this->userUrl());
        $response->assertViewIs('uploadfile::participant');
    }
    
}
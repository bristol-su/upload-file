<?php

namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\AdminApi;

use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Events\StatusChanged;
use BristolSU\Module\UploadFile\Models\File;
use Illuminate\Support\Facades\Event;

class FileStatusControllerTest extends TestCase
{

    /** @test */
    public function it_returns_a_403_status_if_permission_not_owned(){
        $this->revokePermissionTo('uploadfile.admin.status.create');
        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->postJson($this->adminApiUrl('file/' . $file->id . '/status'), ['status' => 'Awaiting Approval']);
        $response->assertStatus(403);
    }

    /** @test */
    public function it_returns_a_404_status_if_file_not_found(){
        $this->bypassAuthorization();
        $response = $this->postJson($this->adminApiUrl('file/100/status'), ['status' => 'Awaiting Approval']);
        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_a_201_status_code_if_permission_owned(){
        $this->givePermissionTo('uploadfile.admin.status.create');
        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->postJson($this->adminApiUrl('file/' . $file->id . '/status'), ['status' => 'Awaiting Approval']);
        $response->assertStatus(201);
    }

    /** @test */
    public function it_creates_a_new_status(){
        $this->givePermissionTo('uploadfile.admin.status.create');
        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->postJson($this->adminApiUrl('file/' . $file->id . '/status'), ['status' => 'SomeStatus']);
        $response->assertStatus(201);

        $this->assertDatabaseHas('uploadfile_file_statuses', [
            'status' => 'SomeStatus',
            'file_id' => $file->id,
            'created_by' => $this->getControlUser()->id()
        ]);
    }

    /** @test */
    public function it_returns_the_new_status(){
        $this->givePermissionTo('uploadfile.admin.status.create');
        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->postJson($this->adminApiUrl('file/' . $file->id . '/status'), ['status' => 'SomeStatus']);
        $response->assertStatus(201);

        $response->assertJsonFragment([
            'status' => 'SomeStatus',
            'file_id' => $file->id,
        ]);
    }

    /** @test */
    public function it_fires_an_event_when_the_status_is_created(){
        Event::fake(StatusChanged::class);
        $this->bypassAuthorization();

        $file = File::factory()->create(['module_instance_id' => $this->getModuleInstance()->id()]);

        $response = $this->postJson($this->adminApiUrl('file/' . $file->id . '/status'), ['status' => 'SomeStatus']);
        $response->assertStatus(201);

        Event::assertDispatched(StatusChanged::class, function($event) use ($file) {
            return $event->file instanceof File && $event->file->is($file);
        });
    }

}

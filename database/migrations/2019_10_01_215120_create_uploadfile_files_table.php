<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadfileFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploadfile_files', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('filename');
            $table->string('mime');
            $table->string('path');
            $table->integer('size');
            $table->unsignedInteger('uploaded_by');
            $table->unsignedInteger('module_instance_id');
            $table->enum('resource_type', ['group', 'user']);
            $table->unsignedInteger('resource_id');
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::table('uploadfile_files', function(Blueprint $table) {
            $table->foreign('uploaded_by')->references('id')->on('users');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uploadfile_files');
    }
}

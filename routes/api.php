<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

Route::namespace('\BristolSU\Module\UploadFile\Http\Controllers\Api')->group(function() {
    Route::get('/me', 'UserController@me');
});
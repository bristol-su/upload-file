<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::namespace('ParticipantApi')->group(function() {
    Route::apiResource('file', 'FileController');
});

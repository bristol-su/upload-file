<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::namespace('ParticipantApi')->group(function() {
    Route::apiResource('file', 'FileController');
    Route::prefix('file/{uploadfile_file}')->group(function() {
        Route::apiResource('comment', 'CommentController');
    });
});

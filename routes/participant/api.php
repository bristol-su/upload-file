<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::namespace('ParticipantApi')->group(function() {
    Route::apiResource('file', 'FileController')->parameters(['file' => 'uploadfile_file']);
    Route::prefix('file/{uploadfile_file}')->group(function() {
        Route::apiResource('comment', 'CommentController')->only(['index', 'store'])->parameters(['comment' => 'uploadfile_comment']);
    });
    Route::apiResource('comment', 'CommentController')->only(['update', 'destroy'])->parameters(['comment' => 'uploadfile_comment']);
});

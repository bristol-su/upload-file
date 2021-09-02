<?php

use BristolSU\Module\UploadFile\Http\Controllers\ParticipantApi\CommentController;
use BristolSU\Module\UploadFile\Http\Controllers\ParticipantApi\FileController;
use BristolSU\Module\UploadFile\Http\Controllers\ParticipantApi\OldFileController;
use Illuminate\Support\Facades\Route;

Route::get('file/old', [OldFileController::class, 'index']);
Route::apiResource('file', FileController::class)->parameters(['file' => 'uploadfile_file']);
Route::prefix('file/{uploadfile_file}')->group(function() {
    Route::apiResource('comment', CommentController::class, ['as' => 'participant'])->only(['index', 'store'])->parameters(['comment' => 'uploadfile_comment']);
});
Route::apiResource('comment', CommentController::class, ['as' => 'participant'])->only(['update', 'destroy'])->parameters(['comment' => 'uploadfile_comment']);

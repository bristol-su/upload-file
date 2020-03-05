<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::namespace('AdminApi')->group(function() {
    Route::apiResource('file', 'FileController')->parameters(['file' => 'uploadfile_file'])->only(['index', 'show']);
    Route::prefix('file/{uploadfile_file}')->group(function() {
        Route::apiResource('status', 'FileStatusController')->parameters(['status' => 'uploadfile_file_status']);
        Route::apiResource('comment', 'CommentController')->only(['index', 'store'])->parameters(['comment' => 'uploadfile_comment']);
    });
    Route::apiResource('comment', 'CommentController')->only(['update', 'destroy'])->parameters(['comment' => 'uploadfile_comment']);
});

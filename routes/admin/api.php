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

use BristolSU\Module\UploadFile\Http\Controllers\AdminApi\ActivityInstanceController;
use BristolSU\Module\UploadFile\Http\Controllers\AdminApi\CommentController;
use BristolSU\Module\UploadFile\Http\Controllers\AdminApi\FileController;
use BristolSU\Module\UploadFile\Http\Controllers\AdminApi\FileStatusController;
use Illuminate\Support\Facades\Route;

Route::apiResource('file', FileController::class, ['as' => 'file.admin'])->parameters(['file' => 'uploadfile_file']);
Route::prefix('file/{uploadfile_file}')->group(function() {
    Route::apiResource('status', FileStatusController::class)->parameters(['status' => 'uploadfile_file_status']);
    Route::apiResource('comment', CommentController::class, ['as' => 'file.admin'])->only(['index', 'store'])->parameters(['comment' => 'uploadfile_comment']);
});
Route::apiResource('comment', CommentController::class, ['as' => 'file.admin'])->only(['update', 'destroy'])->parameters(['comment' => 'uploadfile_comment']);
Route::apiResource('activity-instance', ActivityInstanceController::class)->only(['index']);

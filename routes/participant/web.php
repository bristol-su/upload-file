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

use BristolSU\Module\UploadFile\Http\Controllers\Participant\DownloadFileController;
use BristolSU\Module\UploadFile\Http\Controllers\Participant\ParticipantPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ParticipantPageController::class, 'index']);
Route::get('/file/{uploadfile_file}/download', [DownloadFileController::class, 'download']);
Route::get('/old-file/{uploadfile_old_file}/download', [DownloadFileController::class, 'downloadOld']);

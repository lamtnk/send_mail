<?php

use App\Http\Controllers\SendMailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DataSyncController;
use App\Http\Controllers\DepartmentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
})->name('index');
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Route::get('send-mail', [SendMailController::class, 'sendMail'])->name('sendmail');
Route::middleware(['auth'])->group(function () {

    Route::post('/send-mail', [SendMailController::class, 'sendMail'])->name('sendMail');

    Route::get('data-du-gio', [SendMailController::class, 'dataDugio'])->name('datadugio')->middleware('check.department');

    Route::post('/logout', [GoogleController::class, 'logout'])->name('logout');
    Route::post('/send-all', [SendMailController::class, 'sendAll'])->name('sendAll');
    Route::get('/sync-data', [DataSyncController::class, 'index'])->name('sync.index');
    Route::post('/sync-data', [DataSyncController::class, 'sync'])->name('sync.perform');
    Route::post('/hard-sync', [DataSyncController::class, 'hardSync'])->name('hardSync');
    Route::get('/choose-department', [DepartmentController::class, 'showChooseForm'])->name('department.choose');
    Route::post('/choose-department', [DepartmentController::class, 'saveDepartment'])->name('department.save');
});

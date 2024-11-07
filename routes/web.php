<?php

use App\Http\Controllers\SendMailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

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
    return view('index');
})->name('index');

// Route::get('send-mail', [SendMailController::class, 'sendMail'])->name('sendmail');

Route::post('/send-mail', [SendMailController::class, 'sendMail'])->name('sendMail');

Route::get('data-du-gio', [SendMailController::class, 'dataDugio'])->name('datadugio');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::post('/send-all', [SendMailController::class, 'sendAll'])->name('sendAll');
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

    Route::get('data-du-gio', [SendMailController::class, 'dataDugio'])->name('datadugio');

    Route::get('/logout', [GoogleController::class, 'logout'])->name('logout');
    Route::post('/send-all', [SendMailController::class, 'sendAll'])->name('sendAll');
});
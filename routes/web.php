<?php

use App\Events\MessageSent;
use App\Http\Controllers\Admin\ChatController;
use Illuminate\Support\Facades\Route;
use Pusher\Pusher;




Route::get('/', function () {
    return view('welcome');
});



Route::middleware(['auth'])->group(function () {
    Route::post('/ajax/messages', [ChatController::class,'store'])->name('messages.store');
    Route::get('/messages', [ChatController::class, 'index'])->name('messages.get');
});

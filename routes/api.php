<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EnterpriseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;






// auth routes


Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
    Route::get('/user', 'user')->middleware('auth:sanctum');
    Route::post('/user/update', 'userUpdate')->middleware('auth:sanctum');
    Route::post('/account/delete', 'accountDelete')->middleware('auth:sanctum');

    // forget password
    Route::post('/otp-send','otpSend');
    Route::post('/forget-password','forgetPassword');

});





Route::middleware('auth:sanctum')->group(function () {
    Route::post('/msg-store',[ChatController::class,'store']);

    Route::get('/messages', [ChatController::class,'index']);
});


Route::get('/categories',[CategoryController::class,'index']);
Route::post('/enterprises',[EnterpriseController::class,'store']);
Route::post('/candidates',[CandidateController::class,'store']);
Route::post('/contacts',[ContactController::class,'store']);


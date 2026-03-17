<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bot\ChatbotController;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'bot'], function () {
    Route::get('/chat', [ChatbotController::class, 'index']);
    Route::post('/chat', [ChatbotController::class, 'chat']);
    Route::get('/chat/{sessionId}', [ChatbotController::class, 'chatSession']);
});

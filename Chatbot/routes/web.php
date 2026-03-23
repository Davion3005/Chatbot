<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bot\ChatbotController;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'bot'], function () {
    Route::get('/chat', [ChatbotController::class, 'index']);
    Route::post('/chat', [ChatbotController::class, 'createConversation'])->name('bot.createConversation');
    Route::get('/chat/{conversation}', [ChatbotController::class, 'getConversation'])->name('bot.getConversation');
    Route::post('/chat/{conversation}', [ChatbotController::class, 'stream'])->name('bot.ask');
    Route::post('/chat/{conversation}/stream', [ChatbotController::class, 'stream'])->name('bot.stream');
});

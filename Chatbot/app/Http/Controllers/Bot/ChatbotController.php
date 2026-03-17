<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\BaseController;
use App\Service\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends BaseController
{
    private AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        Log::info('User enter the Chatbot app');
        return view('bot.chat');
    }

    public function chatSession(int $sessionId, Request $request)
    {
        Log::info('User enter the Chatbot session', ['sessionId' => $sessionId]);
        return view('bot.chat-session', ['sessionId' => $sessionId]);
    }

    public function chat(Request $request)
    {
        $message = $request->input('message');
        Log::info('User send message to Chatbot', ['message' => $message]);
        $result = $this->aiService->processMessage($message);
        return response()->json($result);
    }
}

<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreConversationRequest;
use App\Models\Conversation;
use App\Service\ChatService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends BaseController
{

    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(): Factory|View
    {
        Log::info('User enter the Chatbot app');
        return view('bot.chat');
    }

    public function createConversation(StoreConversationRequest $request)
    {
        Log::info('User create new conversation', ['request' => $request->validated()]);
        $initialMessage = $request->input('initial_message');
        $conversation = $this->chatService->createConversation($request->validated());
        return response()->json(['conversation_id' => $conversation->id, 'initial_message' => $initialMessage]);
    }

    public function getConversation(Conversation $conversation, Request $request): Factory|View
    {
        Log::info('User get conversation', ['conversation_id' => $conversation->id]);
        return view('bot.conversation', compact('conversation'));
    }

    public function ask(Conversation $conversation, Request $request): JsonResponse
    {
        $message = $request->input('message');
        Log::info('User send message to Chatbot', ['message' => $message]);
        $result = $this->chatService->ask($message);
        return response()->json($result);
    }
}

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
        return view('bot.index');
    }

    public function createConversation(StoreConversationRequest $request)
    {
        Log::info('User create new conversation', ['request' => $request->validated()]);
        $conversation = $this->chatService->createConversation($request->validated());

        return redirect()
            ->route('bot.getConversation', ['conversation' => $conversation->id]);
    }

    public function getConversation(Conversation $conversation, Request $request): Factory|View
    {
        Log::info('User get conversation', ['conversation_id' => $conversation->id]);
        $conversation->load('messages');
        return view('bot.conversation', compact('conversation'));
    }

    public function ask(Conversation $conversation, Request $request): JsonResponse
    {
        $message = $request->input('message');
        Log::info('User send message to Chatbot', ['message' => $message]);
        $result = $this->chatService->ask($conversation, $message);
        return response()->json($result);
    }

    public function stream(Conversation $conversation, Request $request)
    {
        $message = $request->input('message');
        Log::info('User send message to Chatbot with streaming', ['message' => $message]);
        return $this->chatService->stream($conversation, $message);
    }
}

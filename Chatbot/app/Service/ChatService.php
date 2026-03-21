<?php

namespace App\Service;

use App\Models\Conversation;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function ask(Conversation $conversation, string $message): array
    {
        Log::info('Processing user message in ChatService', ['conversation_id' => $conversation->id, 'message' => $message]);
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $message,
        ]);
        $reply = $this->aiService->processMessage($message);
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $reply['data'],
        ]);

        return $reply;
    }

    public function createConversation($data)
    {
        $conversation = Conversation::create([
            'user_id' => 1, // Assuming user_id is 1 for now, due to the lack of authentication context. In a real application, you would get this from the authenticated user.
            'title' => $data['title'] ?? 'New Conversation ' . now()->format('Y-m-d H:i:s'),
        ]);
        if (isset($data['initial_message'])) {
            $this->createInitialMessages($conversation, $data['initial_message']);
        }
        $conversation->load('messages');

        return $conversation;
    }

    public function createInitialMessages(Conversation $conversation, string $initialMessage)
    {
        $messages[] = $conversation->messages()->create([
            'role' => 'user',
            'content' => $initialMessage,
        ]);

        $messages[] = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $this->ask($conversation, $initialMessage)['data'],
        ]);

        return $messages;
    }
}

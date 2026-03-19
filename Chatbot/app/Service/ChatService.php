<?php

namespace App\Service;

use App\Models\Conversation;

class ChatService
{
    private AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function ask(string $message): array
    {
        return $this->aiService->processMessage($message);
    }

    public function createConversation($data)
    {
        $conversation = Conversation::create([
            'user_id' => 1, // Assuming user_id is 1 for now, due to the lack of authentication context. In a real application, you would get this from the authenticated user.
            'title' => $data['title'] ?? 'New Conversation ' . now()->format('Y-m-d H:i:s'),
        ]);

        return $conversation;
    }
}

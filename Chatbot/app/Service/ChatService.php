<?php

namespace App\Service;

use App\Models\Conversation;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\StreamInterface;

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

    public function stream($conversation, $message)
    {
        Log::info('Processing user message with streaming in ChatService', ['conversation_id' => $conversation->id, 'message' => $message]);
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        return response()->stream(function () use ($message, $conversation) {
            // Must be set before any output
            ini_set('output_buffering', 'off');
            ini_set('zlib.output_compression', false);

            $stream = $this->aiService->processStreamingMessage($message);

            // Handle error string returned from AIService
            if (!($stream instanceof StreamInterface)) {
                echo json_encode(['response' => $stream]) . "\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
                return;
            }

            $buffer = '';
            $fullResponse = '';

            while (!$stream->eof()) {
                $buffer .= $stream->read(1024);

                // Split on newlines, keep incomplete tail for next iteration
                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines);

                foreach ($lines as $jsonLine) {
                    $jsonLine = trim($jsonLine);
                    if ($jsonLine === '') continue;

                    $chunk = json_decode($jsonLine, true);

                    if (json_last_error() === JSON_ERROR_NONE && isset($chunk['response'])) {
                        $fullResponse .= $chunk['response'];
                        // Emit JSON + newline so the FE can parse line-by-line
                        echo json_encode(['response' => $chunk['response']]) . "\n";
                        if (ob_get_level() > 0) ob_flush();
                        flush();
                    }
                }
            }

            // Save the complete AI reply to the database after streaming finishes
            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $fullResponse,
            ]);

        }, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}

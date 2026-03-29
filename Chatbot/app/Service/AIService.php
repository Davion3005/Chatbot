<?php

namespace App\Service;

use App\Service\RAG\RAGService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\StreamInterface;

class AIService
{
    private string $baseUrl;

    private string $model;

    private bool $isShortAnswer;

    public function __construct(protected RAGService $ragService)
    {
        $this->initializeAI();
    }

    private function initializeAI()
    {
        $this->baseUrl = env('AI_API_BASE_URL', 'http://localhost:11434/api');
        $this->model = 'mistral';
        $this->isShortAnswer = env('AI_SHORT_ANSWER', true);
    }

    public function processMessage($message, $files = []): array
    {
        $message = $this->isShortAnswer ? "Please provide a concise answer: $message" : $message;
        if (!empty($files)) {
            $this->ragService->upload($files);
            return $this->ragService->ask($message);
        }
        try {
            $chatEndpoint = $this->baseUrl . '/generate';
            $response = Http::post($chatEndpoint, [
                'model' => $this->model,
                'prompt' => $message,
                'stream' => false
            ]);
            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'status' => 'success',
                    'message' => 'AI response generated successfully',
                    'data' => $responseData['response'] ?? null
                ];
            } else {
                Log::error('AI API request failed', ['status' => $response->status(), 'body' => $response->body()]);
                return [
                    'status' => 'error',
                    'message' => 'Failed to get response from AI API',
                    'data' => null
                ];
            }
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            return [
                'status' => 'error',
                'message' => 'Failed to process message: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function processStreamingMessage($message, $files = []): StreamInterface|string
    {
//        $message = $this->isShortAnswer ? "Please provide a concise answer: $message" : $message;
        if (!empty($files)) {
            $this->ragService->upload($files);
            $ragResponse = $this->ragService->ask($message);

            return $ragResponse;
        }
        try {
            $chatEndpoint = $this->baseUrl . '/generate';
            $response = Http::withOptions([
                'stream' => true
            ])->post($chatEndpoint, [
                'model' => $this->model,
                'prompt' => $message,
                'stream' => true
            ]);

            return $response->getBody();
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            return 'Failed to process message: ' . $e->getMessage();
        }
    }
}

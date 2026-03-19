<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $baseUrl;

    private string $model;
    public function __construct()
    {
        $this->initializeAI();
    }

    private function initializeAI()
    {
        $this->baseUrl = env('AI_API_BASE_URL', 'http://localhost:11434/api');
        $this->model = 'mistral';
    }

    public function processMessage($message)
    {
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
}

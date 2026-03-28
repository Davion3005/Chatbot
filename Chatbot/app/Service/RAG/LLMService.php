<?php

namespace App\Service\RAG;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LLMService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('AI_API_BASE_URL', 'http://localhost:11434/api');
    }

    public function generate(string $context, string $query): ?string
    {
        try {
            $prompt = "Assume you are an assistant that provides answers based on the following context:\n\nContext:\n$context\n\nQuestion:\n$query";
            $response = Http::post($this->baseUrl . '/generate', [
                'model' => 'mistral',
                'prompt' => $prompt,
                'stream' => true
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['response'] ?? null;
            } else {
                Log::error('Failed to generate response', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate response: ' . $e->getMessage());
            return null;
        }
    }
}

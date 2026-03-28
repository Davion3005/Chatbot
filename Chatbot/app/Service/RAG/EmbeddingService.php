<?php

namespace App\Service\RAG;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('AI_API_BASE_URL', 'http://localhost:11434/api');
    }

    public function embed(string $text): ?array
    {
        try {
            $response = Http::post($this->baseUrl . '/embeddings', [
                'model' => 'nomic-embed-text',
                'prompt' => $text,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['embedding'] ?? null;
            } else {
                Log::error('Failed to get embedding', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get embedding: ' . $e->getMessage());
            return null;
        }
    }
}

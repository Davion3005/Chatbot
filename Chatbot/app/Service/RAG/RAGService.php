<?php

namespace App\Service\RAG;

class RAGService
{
    public function __construct(
        protected EmbeddingService $embeddingService,
        protected Retriever $retriever,
        protected LLMService $llmService,
    ) {}

    public function ask(string $query): array
    {
        $queryEmbedding = $this->embeddingService->embed($query);

    }
}

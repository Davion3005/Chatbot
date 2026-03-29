<?php

namespace App\Service\RAG;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class RAGService
{
    public function __construct(protected EmbeddingService $embeddingService, protected Retriever $retriever, protected LLMService $llmService, protected FileProcessor $fileProcessor)
    {
    }

    public function ask(string $query)
    {
        // Step 1: Get embedding for the query
        $queryEmbedding = $this->embeddingService->embed($query);
        if (!$queryEmbedding) {
            return ['error' => 'Failed to get embedding for the query.'];
        }
        // Step 2: Retrieve relevant documents based on the query embedding (search in the vector database)
        $docs = $this->retriever->search($queryEmbedding);
        if (empty($docs)) {
            return ['error' => 'No relevant documents found.'];
        }
        $context = collect($docs)->pluck('content')->implode("\n");
        // Step 3: Generate a response using the retrieved documents as context
        return $this->llmService->generate($context, $query);
    }

    public function upload(array $files)
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $content = $this->fileProcessor->extractText($file);
                $chunks = str_split($content, 2000);
                if (!empty($chunks)) {
                    foreach ($chunks as $chunk) {
                        $embedding = $this->embeddingService->embed($chunk);
                        if ($embedding) {
                            $embedding = '[' . implode(', ', $embedding) . ']';
                            // Store the chunk and its embedding in the database
                            DB::connection('pgsql')->table('documents')->insert(['content' => $chunk, 'embedding' => $embedding,]);
                        } else {
                            return ['error' => 'Failed to get embedding for a chunk of the file.'];
                        }
                    }
                    return ['message' => 'File processed and stored successfully.'];
                } else {
                    return ['error' => 'Invalid file uploaded.'];
                }
            }

            return ['message' => 'Files processed and stored successfully.'];
        }

        return ['error' => 'No files uploaded.'];
    }
}

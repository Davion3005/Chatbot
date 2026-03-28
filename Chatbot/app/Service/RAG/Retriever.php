<?php

namespace App\Service\RAG;

use Illuminate\Support\Facades\DB;

class Retriever
{
    public function search(array $embeddings, int $limit = 5): array
    {
        $embeddingStr = '[' . implode(', ', $embeddings) . ']';
        $query = "SELECT content, embedding <-> '$embeddingStr' AS distance
            FROM documents
            ORDER BY distance ASC
          LIMIT $limit";

        return DB::connection('pgsql')->select($query);
    }

}

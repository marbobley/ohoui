<?php

declare(strict_types=1);

namespace App\Service;

use Solarium\QueryType\Select\Result\Result;

interface SolrClientServiceInterface
{
    public function search(string $query, int $start = 0, int $rows = 10): Result;

    /**
     * @param array<string, mixed> $data
     */
    public function indexDocument(array $data): void;
}

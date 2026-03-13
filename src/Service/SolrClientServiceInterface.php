<?php

declare(strict_types=1);

namespace App\Service;

use App\Infrastructure\Solr\SolrResponse;

interface SolrClientServiceInterface
{
    /**
     * @param array<string, string> $filters
     */
    public function search(string $query, int $start = 0, int $rows = 10, array $filters = []): SolrResponse;

    /**
     * @param array<string, mixed> $data
     */
    public function indexDocument(array $data): void;
}

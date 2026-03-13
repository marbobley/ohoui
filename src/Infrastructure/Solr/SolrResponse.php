<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

class SolrResponse
{
    /**
     * @param list<array<array-key, mixed>> $documents
     */
    public function __construct(
        private array $documents,
        private int $numFound,
    ) {}

    /**
     * @return list<array<array-key, mixed>>
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function getNumFound(): int
    {
        return $this->numFound;
    }
}

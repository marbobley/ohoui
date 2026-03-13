<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

class SolrResponse
{
    /**
     * @param list<array<array-key, mixed>> $documents
     * @param array<string, array<string, int>> $facets
     */
    public function __construct(
        private array $documents,
        private int $numFound,
        private array $facets,
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

    /**
     * @return array<string, array<string, int>>
     */
    public function getFacets(): array
    {
        return $this->facets;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Model;

final readonly class SearchResult
{
    /**
     * @param Document[] $documents
     * @param Facet[] $facets
     */
    public function __construct(
        private array $documents,
        private int $totalCount,
        private int $limit,
        private int $offset,
        private array $facets = [],
    ) {}

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @return Facet[]
     */
    public function getFacets(): array
    {
        return $this->facets;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}

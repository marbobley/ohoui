<?php

declare(strict_types=1);

namespace App\Domain\Model;

final readonly class SearchResult
{
    /**
     * @param Document[] $documents
     */
    public function __construct(
        private array $documents,
        private int $totalCount,
        private int $limit,
        private int $offset,
    ) {}

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
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

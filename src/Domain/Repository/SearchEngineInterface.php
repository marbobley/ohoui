<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\Document;
use App\Domain\Model\SearchResult;

interface SearchEngineInterface
{
    public function index(Document $document): void;
    public function purge(): void;

    /**
     * @param array<string, string> $filters
     */
    public function search(string $query, int $offset = 0, int $limit = 10, array $filters = []): SearchResult;

    /**
     * @return array<string, mixed>
     */
    public function getStatus(): array;
}

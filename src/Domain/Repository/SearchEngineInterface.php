<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\Document;
use App\Domain\Model\SearchCriteria;
use App\Domain\Model\SearchResult;

interface SearchEngineInterface
{
    public function index(Document $document): void;

    public function purge(): void;

    public function search(SearchCriteria $criteria): SearchResult;

    /**
     * @return array<string, mixed>
     */
    public function getStatus(): array;
}

<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Model\SearchCriteria;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;

final readonly class SearchUseCase
{
    public function __construct(
        private SearchEngineInterface $searchEngine,
    ) {}

    public function execute(SearchCriteria $criteria): SearchResult
    {
        return $this->searchEngine->search($criteria);
    }
}

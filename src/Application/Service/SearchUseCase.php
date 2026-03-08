<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Model\Document;
use App\Domain\Repository\SearchEngineInterface;

final readonly class SearchUseCase
{
    public function __construct(
        private SearchEngineInterface $searchEngine,
    ) {}

    /**
     * @return Document[]
     */
    public function execute(string $query): array
    {
        return $this->searchEngine->search($query);
    }
}

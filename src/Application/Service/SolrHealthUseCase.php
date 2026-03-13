<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Repository\SearchEngineInterface;

readonly class SolrHealthUseCase
{
    public function __construct(
        private SearchEngineInterface $searchEngine,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return $this->searchEngine->getStatus();
    }
}

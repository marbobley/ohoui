<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Repository\SearchEngineInterface;

final readonly class PurgeUseCase
{
    public function __construct(
        private SearchEngineInterface $searchEngine,
    ) {}

    public function execute(): void
    {
        $this->searchEngine->purge();
    }
}

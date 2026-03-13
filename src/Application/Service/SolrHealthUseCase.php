<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Repository\SearchEngineInterface;
use Override;

final readonly class SolrHealthUseCase implements SolrHealthUseCaseInterface
{
    public function __construct(
        private SearchEngineInterface $searchEngine,
    ) {}

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function execute(): array
    {
        return $this->searchEngine->getStatus();
    }
}

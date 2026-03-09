<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Model\Document;
use App\Domain\Repository\SearchEngineInterface;

final readonly class IndexUseCase
{
    public function __construct(
        private SearchEngineInterface $searchEngine,
    ) {}

    public function execute(Document $document): void
    {
        $this->searchEngine->index($document);
    }
}

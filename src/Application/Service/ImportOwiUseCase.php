<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Repository\OwiDataExtractorInterface;
use App\Domain\Repository\SearchEngineInterface;

/**
 * Cas d'utilisation pour orchestrer l'importation de données Owilix.
 */
class ImportOwiUseCase
{
    public function __construct(
        private OwiDataExtractorInterface $owiExtractor,
        private SearchEngineInterface $searchEngine,
    ) {}

    /**
     * @param string $datasetId
     * @param int $limit
     * @return int Le nombre de documents effectivement indexés.
     */
    public function execute(string $datasetId, int $limit): int
    {
        $documents = $this->owiExtractor->extract($datasetId, $limit);
        $count = 0;

        foreach ($documents as $document) {
            $this->searchEngine->index($document);
            $count++;
        }

        return $count;
    }
}

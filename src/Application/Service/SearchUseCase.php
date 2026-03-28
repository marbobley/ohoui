<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\CategorizedSearchResults;
use App\Domain\Model\SearchCriteria;
use App\Domain\Repository\SearchEngineInterface;
use Webmozart\Assert\InvalidArgumentException;

use function shuffle;
use function usort;

final readonly class SearchUseCase
{
    public function __construct(
        private SearchEngineInterface $searchEngine,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function execute(SearchCriteria $criteria): CategorizedSearchResults
    {
        $result = $this->searchEngine->search($criteria);
        $documents = $result->getDocuments();

        // 1. Les plus pertinents (ceux renvoyés par Solr dans l'ordre par défaut)
        $relevant = $documents;

        // 2. Les plus récents (triés par indexedAt)
        $recentDocs = $documents;
        usort(
            $recentDocs,
            static fn($a, $b) => (
                ($b->getIndexedAt()?->getTimestamp() ?? 0)
                <=> ($a->getIndexedAt()?->getTimestamp() ?? 0)
            ),
        );
        $recent = $recentDocs;

        // 3. Aléatoire dans les résultats
        $randomDocs = $documents;
        shuffle($randomDocs);
        $random = $randomDocs;

        return new CategorizedSearchResults($relevant, $recent, $random, $result->getTotalCount());
    }
}

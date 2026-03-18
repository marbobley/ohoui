<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use App\Domain\Model\Document;
use App\Domain\Model\SearchCriteria;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;
use App\Service\SolrClientServiceInterface;
use App\Service\StringHandlerInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Override;

use function is_array;
use function reset;

readonly class SolrSearchEngine implements SearchEngineInterface
{
    public function __construct(
        private SolrClientServiceInterface $solrClientService,
        private StringHandlerInterface $stringHandler,
    ) {}

    #[Override]
    public function index(Document $document): void
    {
        $this->solrClientService->indexDocument([
            'id' => $document->getId(),
            'title' => $document->getTitle(),
            'url' => $document->getUrl(),
            'content' => $document->getContent(),
            'language' => $document->getLanguage(),
            'domain' => $document->getDomain(),
            'indexed_at_dt' => $document->getIndexedAt()?->format(DateTimeInterface::ATOM),
        ]);
    }

    #[Override]
    public function purge(): void
    {
        $this->solrClientService->purge();
    }

    #[Override]
    public function search(SearchCriteria $criteria): SearchResult
    {
        $response = $this->solrClientService->search(
            $criteria->getQuery(),
            $criteria->getOffset(),
            $criteria->getLimit(),
            $criteria->getFilters(),
        );

        $documents = [];
        foreach ($response->documents as $docData) {
            $id = (string) ($docData['id'] ?? '');
            $highlight = null;

            $docHighlighting = $response->highlighting[$id] ?? [];
            $contentHighlights = $docHighlighting['content'] ?? [];

            if ([] !== $contentHighlights) {
                $highlight = (string) reset($contentHighlights);
                $highlight = $this->stringHandler->sanitizeHighlight($highlight, '<em class="hl">', '</em>');
            }

            $documents[] = $this->mapToDocument($docData, $highlight);
        }

        return new SearchResult($documents, $response->numFound, $criteria->getLimit(), $criteria->getOffset());
    }

    #[Override]
    public function getStatus(): array
    {
        return $this->solrClientService->getStatus();
    }

    /**
     * @param array<array-key, mixed> $docData
     */
    private function mapToDocument(array $docData, ?string $highlight = null): Document
    {
        $indexedAt = null;
        if (($docData['indexed_at_dt'] ?? null) !== null) {
            /** @var mixed $dateValue */
            $dateValue = $docData['indexed_at_dt'];
            $dateStr = is_array($dateValue) ? (string) reset($dateValue) : (string) $dateValue;
            $dateTime = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $dateStr);
            $indexedAt = $dateTime instanceof DateTimeImmutable ? $dateTime : null;
        }

        return new Document(
            $this->stringHandler->extractStringValue($docData, 'id'),
            $this->stringHandler->extractStringValue($docData, 'title'),
            $this->stringHandler->extractStringValue($docData, 'url'),
            $this->stringHandler->extractStringValue($docData, 'content'),
            $this->stringHandler->extractStringValue($docData, 'language'),
            $this->stringHandler->extractStringValue($docData, 'domain'),
            $highlight,
            $indexedAt,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use App\Domain\Model\Document;
use App\Domain\Model\SearchCriteria;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;
use App\Service\SolrClientServiceInterface;
use DateTimeImmutable;
use Override;

use function htmlspecialchars;
use function is_array;
use function reset;
use function str_replace;

readonly class SolrSearchEngine implements SearchEngineInterface
{
    public function __construct(
        private SolrClientServiceInterface $solrClientService,
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
            'indexed_at' => $document->getIndexedAt()?->format(DateTimeImmutable::ATOM),
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
                $highlight = $this->sanitizeHighlight($highlight);
            }

            $documents[] = $this->mapToDocument($docData, $highlight);
        }

        return new SearchResult($documents, $response->numFound, $criteria->getLimit(), $criteria->getOffset());
    }

    private function sanitizeHighlight(string $highlight): string
    {
        // On définit les balises de confiance utilisées par SolrQueryBuilder
        $prefix = '<em class="hl">';
        $postfix = '</em>';

        // On échappe tout le HTML de manière sécurisée
        $sanitized = htmlspecialchars($highlight, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8');

        // On ré-autorise uniquement les balises EXACTES générées par Solr
        // L'échappement par htmlspecialchars transforme les balises en &lt;em class=&quot;hl&quot;&gt;
        return str_replace(
            [
                htmlspecialchars($prefix, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8'),
                htmlspecialchars($postfix, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8'),
            ],
            [
                $prefix,
                $postfix,
            ],
            $sanitized,
        );
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
        if (($docData['indexed_at'] ?? null) !== null) {
            /** @var mixed $dateValue */
            $dateValue = $docData['indexed_at'];
            $dateStr = is_array($dateValue) ? (string) reset($dateValue) : (string) $dateValue;
            $dateTime = DateTimeImmutable::createFromFormat(DateTimeImmutable::ATOM, $dateStr);
            $indexedAt = $dateTime instanceof DateTimeImmutable ? $dateTime : null;
        }

        return new Document(
            $this->extractStringValue($docData, 'id'),
            $this->extractStringValue($docData, 'title'),
            $this->extractStringValue($docData, 'url'),
            $this->extractStringValue($docData, 'content'),
            $this->extractStringValue($docData, 'language'),
            $this->extractStringValue($docData, 'domain'),
            $highlight,
            $indexedAt,
        );
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function extractStringValue(array $data, string $key): string
    {
        /** @var mixed $value */
        $value = $data[$key] ?? '';

        return is_array($value) ? (string) reset($value) : (string) $value;
    }
}

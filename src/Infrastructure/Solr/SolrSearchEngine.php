<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use App\Domain\Model\Document;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;
use App\Service\SolrClientServiceInterface;
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
        ]);
    }

    #[Override]
    public function purge(): void
    {
        $this->solrClientService->purge();
    }

    #[Override]
    public function search(string $query, int $offset = 0, int $limit = 10, array $filters = []): SearchResult
    {
        $response = $this->solrClientService->search($query, $offset, $limit, $filters);

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

        return new SearchResult($documents, $response->numFound, $limit, $offset);
    }

    private function sanitizeHighlight(string $highlight): string
    {
        // On échappe tout le HTML
        $sanitized = htmlspecialchars($highlight, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8');

        // On ré-autorise uniquement les balises <em> avec la classe "hl" injectées par Solr
        return str_replace(
            [
                htmlspecialchars('<em class="hl">', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8'),
                htmlspecialchars('</em>', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8'),
            ],
            [
                '<em class="hl">',
                '</em>',
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
        return new Document(
            $this->extractStringValue($docData, 'id'),
            $this->extractStringValue($docData, 'title'),
            $this->extractStringValue($docData, 'url'),
            $this->extractStringValue($docData, 'content'),
            $this->extractStringValue($docData, 'language'),
            $this->extractStringValue($docData, 'domain'),
            $highlight,
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

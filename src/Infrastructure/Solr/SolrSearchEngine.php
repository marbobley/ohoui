<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use App\Domain\Model\Document;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;
use App\Service\SolrClientServiceInterface;
use Override;

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
    public function search(string $query, int $offset = 0, int $limit = 10, array $filters = []): SearchResult
    {
        $response = $this->solrClientService->search($query, $offset, $limit, $filters);

        $documents = [];
        foreach ($response->getDocuments() as $docData) {
            $documents[] = $this->mapToDocument($docData);
        }

        return new SearchResult($documents, $response->getNumFound(), $limit, $offset);
    }

    /**
     * @param array<array-key, mixed> $docData
     */
    private function mapToDocument(array $docData): Document
    {
        return new Document(
            $this->extractStringValue($docData, 'id'),
            $this->extractStringValue($docData, 'title'),
            $this->extractStringValue($docData, 'url'),
            $this->extractStringValue($docData, 'content'),
            $this->extractStringValue($docData, 'language'),
            $this->extractStringValue($docData, 'domain'),
        );
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function extractStringValue(array $data, string $key): string
    {
        /** @var mixed $value */
        $value = $data[$key] ?? '';

        return \is_array($value) ? (string) \reset($value) : (string) $value;
    }
}

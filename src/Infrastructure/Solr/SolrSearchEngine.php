<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use App\Domain\Enum\SearchFacet;
use App\Domain\Model\Document;
use App\Domain\Model\Facet;
use App\Domain\Model\FacetValue;
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

        return new SearchResult(
            $documents,
            $response->getNumFound(),
            $limit,
            $offset,
            $this->mapFacets($response->getFacets()),
        );
    }

    /**
     * @param array<string, array<string, int>> $facetsData
     * @return Facet[]
     */
    private function mapFacets(array $facetsData): array
    {
        $facets = [];
        foreach ($facetsData as $facetName => $valuesData) {
            $values = [];
            foreach ($valuesData as $value => $count) {
                $values[] = new FacetValue($value, $count);
            }

            if ($values !== []) {
                $label = SearchFacet::tryFromLabel($facetName);
                $facets[] = new Facet($facetName, $label, $values);
            }
        }

        return $facets;
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

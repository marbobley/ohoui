<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use App\Domain\Model\Document;
use App\Domain\Model\Facet;
use App\Domain\Model\FacetValue;
use App\Domain\Enum\SearchFacet;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;
use App\Service\SolrClientServiceInterface;
use Override;
use Solarium\Component\Result\Facet\Field as SolariumFacetField;
use Solarium\QueryType\Select\Result\Document as SolariumDocument;

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
        $result = $this->solrClientService->search($query, $offset, $limit, $filters);

        $documents = [];
        /** @var SolariumDocument $doc */
        foreach ($result as $doc) {
            $documents[] = $this->mapToDocument($doc);
        }

        return new SearchResult(
            $documents,
            $result->getNumFound() ?? 0,
            $limit,
            $offset,
            $this->mapFacets($result)
        );
    }

    /**
     * @return Facet[]
     */
    private function mapFacets(\Solarium\QueryType\Select\Result\Result $result): array
    {
        $facetSet = $result->getFacetSet();
        if ($facetSet === null) {
            return [];
        }

        $facets = [];
        /**
         * @var string $facetName
         * @var mixed $facet
         */
        foreach ($facetSet as $facetName => $facet) {
            if (!$facet instanceof SolariumFacetField) {
                continue;
            }

            $values = [];
            /** @var int $count */
            foreach ($facet as $value => $count) {
                $values[] = new FacetValue((string) $value, $count);
            }

            if ($values !== []) {
                $label = SearchFacet::tryFromLabel($facetName);
                $facets[] = new Facet($facetName, $label, $values);
            }
        }

        return $facets;
    }

    private function mapToDocument(SolariumDocument $doc): Document
    {
        /** @var array<string, mixed> $docData */
        $docData = $doc->getFields();

        return new Document(
            $this->extractStringValue($docData, 'id'),
            $this->extractStringValue($docData, 'title'),
            $this->extractStringValue($docData, 'url'),
            $this->extractStringValue($docData, 'content'),
            $this->extractStringValue($docData, 'language'),
            $this->extractStringValue($docData, 'domain')
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractStringValue(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return \is_array($value) ? (string) \reset($value) : (string) $value;
    }
}

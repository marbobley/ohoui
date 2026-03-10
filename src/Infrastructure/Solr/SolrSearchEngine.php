<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use App\Domain\Model\Document;
use App\Domain\Model\Facet;
use App\Domain\Model\FacetValue;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;
use App\Service\SolrClientServiceInterface;

readonly class SolrSearchEngine implements SearchEngineInterface
{
    public function __construct(
        private SolrClientServiceInterface $solrClientService,
    ) {}

    #[\Override]
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

    #[\Override]
    public function search(string $query, int $offset = 0, int $limit = 10, array $filters = []): SearchResult
    {
        $result = $this->solrClientService->search($query, $offset, $limit, $filters);

        $documents = [];
        /** @var \Solarium\QueryType\Select\Result\Document $doc */
        foreach ($result as $doc) {
            $documents[] = $this->mapToDocument($doc);
        }

        $facets = [];
        $facetSet = $result->getFacetSet();
        if ($facetSet !== null) {
            /**
             * @var string $facetName
             * @var mixed $facet
             */
            foreach ($facetSet as $facetName => $facet) {
                if (!$facet instanceof \Solarium\Component\Result\Facet\Field) {
                    continue;
                }

                $values = [];
                /** @var int $count */
                foreach ($facet as $value => $count) {
                    $values[] = new FacetValue((string) $value, $count);
                }

                if ($values !== []) {
                    $label = match ($facetName) {
                        'language' => 'Langue',
                        'domain' => 'Domaine',
                        default => $facetName,
                    };
                    $facets[] = new Facet($facetName, $label, $values);
                }
            }
        }

        return new SearchResult($documents, $result->getNumFound() ?? 0, $limit, $offset, $facets);
    }

    private function mapToDocument(\Solarium\QueryType\Select\Result\Document $doc): Document
    {
        /** @var array<string, mixed> $docData */
        $docData = $doc->getFields();

        $id = $this->extractStringValue($docData, 'id');
        $title = $this->extractStringValue($docData, 'title');
        $url = $this->extractStringValue($docData, 'url');
        $content = $this->extractStringValue($docData, 'content');
        $language = $this->extractStringValue($docData, 'language');
        $domain = $this->extractStringValue($docData, 'domain');

        return new Document($id, $title, $url, $content, $language, $domain);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractStringValue(array $data, string $key): string
    {
        /** @var string|string[] $value */
        $value = $data[$key] ?? '';
        if (\is_array($value)) {
            $value = (string) \reset($value);
        }

        return $value;
    }
}

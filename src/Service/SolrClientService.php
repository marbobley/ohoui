<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Enum\SearchFacet;
use App\Domain\Repository\SearchEngineInterface;
use Override;
use Solarium\Client;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Update\Query\Document;
use Solarium\QueryType\Update\Query\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function sprintf;
use function str_contains;

class SolrClientService implements SolrClientServiceInterface
{
    public function __construct(
        private readonly Client $client,
    ) {}

    #[Override]
    public function search(string $query, int $start = 0, int $rows = 10, array $filters = []): Result
    {
        /** @var \Solarium\QueryType\Select\Query\Query $select */
        $select = $this->client->createSelect();

        $this->configureFacets($select);
        $this->configureQuery($select, $query);
        $this->configureFilters($select, $filters);

        $select->setStart($start);
        $select->setRows($rows);

        /** @var Result */
        return $this->client->select($select);
    }

    private function configureFacets(\Solarium\QueryType\Select\Query\Query $select): void
    {
        $facetSet = $select->getFacetSet();
        foreach (SearchFacet::cases() as $facetEnum) {
            /** @var \Solarium\Component\Facet\Field $facet */
            $facet = $facetSet->createFacetField($facetEnum->value);
            $facet->setField($facetEnum->value);
        }
    }

    private function configureQuery(\Solarium\QueryType\Select\Query\Query $select, string $query): void
    {
        if (!str_contains($query, ':')) {
            $helper = $select->getHelper();
            $escapedQuery = $helper->escapeTerm($query);
            // On cherche dans le titre avec un boost de 2.0 et dans le contenu par défaut
            $query = sprintf('title:"%1$s"^2.0 OR content:"%1$s"', $escapedQuery);
        }

        $select->setQuery($query);
    }

    private function configureFilters(\Solarium\QueryType\Select\Query\Query $select, array $filters): void
    {
        foreach ($filters as $field => $value) {
            $select->createFilterQuery($field)->setQuery(sprintf('%s:%s', $field, $value));
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    #[Override]
    public function indexDocument(array $data): void
    {
        /** @var Query $update */
        $update = $this->client->createUpdate();
        /** @var Document $doc */
        $doc = $update->createDocument();

        /** @var string $value */
        foreach ($data as $key => $value) {
            $doc->setField($key, $value);
        }

        $update->addDocument($doc);
        $update->addCommit();

        $this->client->update($update);
    }
}

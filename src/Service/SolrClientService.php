<?php

declare(strict_types=1);

namespace App\Service;

use Override;
use Solarium\Client;
use Solarium\Core\Client\Adapter\AdapterInterface;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Update\Query\Document;
use Solarium\QueryType\Update\Query\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function sprintf;
use function str_contains;

class SolrClientService implements SolrClientServiceInterface
{
    private Client $client;

    public function __construct(
        AdapterInterface $adapter,
        EventDispatcherInterface $eventDispatcher,
        string $solrHost,
        int $solrPort,
        string $solrPath,
        string $solrCore,
    ) {
        $options = [
            'endpoint' => [
                'main' => [
                    'host' => $solrHost,
                    'port' => $solrPort,
                    'path' => $solrPath,
                    'core' => $solrCore,
                ],
            ],
        ];

        $this->client = new Client($adapter, $eventDispatcher, $options);
    }

    #[Override]
    public function search(string $query, int $start = 0, int $rows = 10, array $filters = []): Result
    {
        /** @var \Solarium\QueryType\Select\Query\Query $select */
        $select = $this->client->createSelect();

        $facetSet = $select->getFacetSet();
        /** @var \Solarium\Component\Facet\Field $languageFacet */
        $languageFacet = $facetSet->createFacetField('language');
        $languageFacet->setField('language');

        /** @var \Solarium\Component\Facet\Field $domainFacet */
        $domainFacet = $facetSet->createFacetField('domain');
        $domainFacet->setField('domain');

        if (!str_contains($query, ':')) {
            $helper = $select->getHelper();
            $escapedQuery = $helper->escapeTerm($query);
            // On cherche dans le titre avec un boost de 2.0 et dans le contenu par défaut
            $query = sprintf('title:"%1$s"^2.0 OR content:"%1$s"', $escapedQuery);
        }

        $select->setQuery($query);

        foreach ($filters as $field => $value) {
            $select->createFilterQuery($field)->setQuery(sprintf('%s:%s', $field, $value));
        }

        $select->setStart($start);
        $select->setRows($rows);

        /** @var Result */
        return $this->client->select($select);
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

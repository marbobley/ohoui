<?php

declare(strict_types=1);

namespace App\Service;

use App\Infrastructure\Solr\SolrDataMapperInterface;
use App\Infrastructure\Solr\SolrQueryBuilderInterface;
use App\Infrastructure\Solr\SolrResponse;
use Override;
use Solarium\Client;
use Solarium\QueryType\Update\Query\Document;
use Solarium\QueryType\Update\Query\Query;

class SolrClientService implements SolrClientServiceInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly SolrQueryBuilderInterface $queryBuilder,
        private readonly SolrDataMapperInterface $dataMapper,
    ) {}

    #[Override]
    public function search(string $query, int $start = 0, int $rows = 10, array $filters = []): SolrResponse
    {
        /** @var \Solarium\QueryType\Select\Query\Query $select */
        $select = $this->client->createSelect();

        $this->queryBuilder->build($select, $query, $start, $rows, $filters);

        /** @var \Solarium\QueryType\Select\Result\Result $result */
        $result = $this->client->select($select);

        return $this->dataMapper->mapResponse($result);
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

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

    #[Override]
    public function purge(): void
    {
        /** @var Query $update */
        $update = $this->client->createUpdate();
        $update->addDeleteQuery('*:*');
        $update->addCommit();

        $this->client->update($update);
    }

    #[Override]
    public function getStatus(): array
    {
        $adminQuery = $this->client->createCoreAdmin();
        $coreName = $this->client->getOptions()['endpoint']['localhost']['core'] ?? 'unknown';
        $statusAction = $adminQuery->createStatus();
        $statusAction->setCore($coreName);
        $adminQuery->setAction($statusAction);

        /** @var \Solarium\QueryType\Server\CoreAdmin\Result\Result $result */
        $result = $this->client->coreAdmin($adminQuery);
        $statusResult = $result->getStatusResult();

        $select = $this->client->createSelect();
        $select->setRows(0);
        $selectResult = $this->client->select($select);

        return [
            'core_name' => $coreName,
            'num_docs' => $selectResult->getNumFound(),
            'index_size' => 'N/A', // Solarium StatusResult doesn't seem to expose size directly in a simple way in this version
            'last_modified' => $statusResult?->getLastModified()?->format('Y-m-d H:i:s') ?? 'N/A',
            'uptime' => $statusResult?->getUptime() ?? 'N/A',
        ];
    }
}

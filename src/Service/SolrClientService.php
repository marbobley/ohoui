<?php

declare(strict_types=1);

namespace App\Service;

use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SolrClientService
{
    private Client $client;

    public function __construct(string $solrHost, int $solrPort, string $solrPath, string $solrCore)
    {
        $adapter = new Curl();
        $eventDispatcher = new EventDispatcher();
        $options = [
            'endpoint' => [
                'localhost' => [
                    'host' => $solrHost,
                    'port' => $solrPort,
                    'path' => $solrPath,
                    'core' => $solrCore,
                ]
            ]
        ];

        $this->client = new Client($adapter, $eventDispatcher, $options);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function search(string $query, int $start = 0, int $rows = 10): \Solarium\QueryType\Select\Result\Result
    {
        $select = $this->client->createSelect();
        $select->setQuery($query);
        $select->setStart($start);
        $select->setRows($rows);

        return $this->client->select($select);
    }

    public function indexDocument(array $data): void
    {
        $update = $this->client->createUpdate();
        $doc = $update->createDocument();

        foreach ($data as $key => $value) {
            $doc->setField($key, $value);
        }

        $update->addDocument($doc);
        $update->addCommit();

        $this->client->update($update);
    }
}

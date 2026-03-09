<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use App\Domain\Model\Document;
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
    public function search(string $query): array
    {
        $result = $this->solrClientService->search($query);

        $documents = [];
        foreach ($result as $doc) {
            /** @var \Solarium\QueryType\Select\Result\Document $doc */
            $docData = $doc->getFields();
            $documents[] = new Document(
                (string) ($docData['id'] ?? ''),
                (string) ($docData['title'] ?? ''),
                (string) ($docData['url'] ?? ''),
                (string) ($docData['content'] ?? ''),
                (string) ($docData['language'] ?? ''),
                (string) ($docData['domain'] ?? ''),
            );
        }

        return $documents;
    }
}

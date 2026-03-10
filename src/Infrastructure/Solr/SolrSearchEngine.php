<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use App\Domain\Model\Document;
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
    public function search(string $query, int $offset = 0, int $limit = 10): SearchResult
    {
        $result = $this->solrClientService->search($query, $offset, $limit);

        $documents = [];
        foreach ($result as $doc) {
            /** @var \Solarium\QueryType\Select\Result\Document $doc */
            $docData = $doc->getFields();

            /** @var string|string[] $id */
            $id = $docData['id'] ?? '';
            if (\is_array($id)) {
                $id = (string) \reset($id);
            }

            /** @var string|string[] $title */
            $title = $docData['title'] ?? '';
            if (\is_array($title)) {
                $title = (string) \reset($title);
            }

            /** @var string|string[] $url */
            $url = $docData['url'] ?? '';
            if (\is_array($url)) {
                $url = (string) \reset($url);
            }

            /** @var string|string[] $content */
            $content = $docData['content'] ?? '';
            if (\is_array($content)) {
                $content = (string) \reset($content);
            }

            /** @var string|string[] $language */
            $language = $docData['language'] ?? '';
            if (\is_array($language)) {
                $language = (string) \reset($language);
            }

            /** @var string|string[] $domain */
            $domain = $docData['domain'] ?? '';
            if (\is_array($domain)) {
                $domain = (string) \reset($domain);
            }

            $documents[] = new Document($id, $title, $url, $content, $language, $domain);
        }

        return new SearchResult($documents, $result->getNumFound() ?? 0, $limit, $offset);
    }
}

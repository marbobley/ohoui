<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Solr;

use App\Infrastructure\Solr\SolrResponse;
use App\Infrastructure\Solr\SolrSearchEngine;
use App\Service\SolrClientServiceInterface;
use App\Tests\Util\DocumentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SolrSearchEngineTest extends TestCase
{
    private SolrClientServiceInterface&MockObject $solrClientServiceMock;
    private SolrSearchEngine $solrSearchEngine;

    protected function setUp(): void
    {
        $this->solrClientServiceMock = $this->createMock(SolrClientServiceInterface::class);
        $this->solrSearchEngine = new SolrSearchEngine($this->solrClientServiceMock);
    }

    public function testIndexDelegatesToClientService(): void
    {
        $document = DocumentFactory::create(
            title: 'Test Title',
            url: 'https://test.com',
            content: 'Test Content',
            domain: 'test.com'
        );

        $this->solrClientServiceMock
            ->expects(self::once())
            ->method('indexDocument')
            ->with([
                'id' => '1',
                'title' => 'Test Title',
                'url' => 'https://test.com',
                'content' => 'Test Content',
                'language' => 'fr',
                'domain' => 'test.com',
            ]);

        $this->solrSearchEngine->index($document);
    }

    public function testSearchDelegatesToClientService(): void
    {
        $query = 'test';
        $offset = 10;
        $limit = 20;
        $filters = ['domain' => 'example.com'];
        $responseStub = new SolrResponse([
            [
                'id' => 'doc-1',
                'title' => 'Title 1',
                'url' => 'https://t1.com',
                'content' => 'Content with test keyword',
                'language' => 'en',
                'domain' => 't1.com',
            ],
        ], 1, [
            'doc-1' => [
                'content' => ['Content with <em class="hl">test</em> keyword'],
            ],
        ]);

        $this->solrClientServiceMock
            ->expects(self::once())
            ->method('search')
            ->with($query, $offset, $limit, $filters)
            ->willReturn($responseStub);

        $results = $this->solrSearchEngine->search($query, $offset, $limit, $filters);

        self::assertCount(1, $results->getDocuments());
        $doc = $results->getDocuments()[0];
        self::assertEquals('doc-1', $doc->getId());
        self::assertEquals('Content with <em class="hl">test</em> keyword', $doc->getHighlight());
        self::assertEquals(1, $results->getTotalCount());
        self::assertEquals($offset, $results->getOffset());
        self::assertEquals($limit, $results->getLimit());
    }
}

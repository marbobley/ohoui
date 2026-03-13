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
        $responseStub = $this->createStub(SolrResponse::class);

        $this->solrClientServiceMock
            ->expects(self::once())
            ->method('search')
            ->with($query, $offset, $limit, $filters)
            ->willReturn($responseStub);

        $responseStub->method('getDocuments')->willReturn([]);
        $responseStub->method('getNumFound')->willReturn(0);
        $responseStub->method('getFacets')->willReturn([]);

        $results = $this->solrSearchEngine->search($query, $offset, $limit, $filters);

        self::assertEmpty($results->getDocuments());
        self::assertEquals(0, $results->getTotalCount());
        self::assertEquals($offset, $results->getOffset());
        self::assertEquals($limit, $results->getLimit());
    }

    public function testSearchMapsFacets(): void
    {
        $query = 'test';
        $responseStub = $this->createMock(SolrResponse::class);

        $this->solrClientServiceMock
            ->method('search')
            ->willReturn($responseStub);

        $responseStub->method('getDocuments')->willReturn([]);
        $responseStub->method('getNumFound')->willReturn(0);
        $responseStub->method('getFacets')->willReturn([
            'language' => ['fr' => 5, 'en' => 2],
        ]);

        $results = $this->solrSearchEngine->search($query);

        self::assertCount(1, $results->getFacets());
        $facet = $results->getFacets()[0];
        self::assertSame('language', $facet->getName());
        self::assertSame('Langue', $facet->getLabel());
        self::assertCount(2, $facet->getValues());
        self::assertSame('fr', $facet->getValues()[0]->getValue());
        self::assertSame(5, $facet->getValues()[0]->getCount());
    }
}

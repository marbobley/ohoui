<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Solr;

use App\Domain\Model\Document;
use App\Domain\Model\SearchResult;
use App\Infrastructure\Solr\SolrSearchEngine;
use App\Service\SolrClientServiceInterface;
use App\Tests\Util\DocumentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Select\Result\Result;

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
            id: '1',
            title: 'Test Title',
            url: 'https://test.com',
            content: 'Test Content',
            language: 'fr',
            domain: 'test.com'
        );

        $this->solrClientServiceMock
            ->expects($this->once())
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
        $resultStub = $this->createStub(Result::class);

        $this->solrClientServiceMock
            ->expects($this->once())
            ->method('search')
            ->with($query)
            ->willReturn($resultStub);

        $resultStub->method('getIterator')->willReturn(new \ArrayIterator([]));
        $resultStub->method('getNumFound')->willReturn(0);

        $results = $this->solrSearchEngine->search($query);

        static::assertInstanceOf(SearchResult::class, $results);
        static::assertEmpty($results->getDocuments());
        static::assertEquals(0, $results->getTotalCount());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Solr;

use App\Infrastructure\Solr\SolrSearchEngine;
use App\Service\SolrClientServiceInterface;
use App\Tests\Util\DocumentFactory;
use ArrayIterator;use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solarium\Component\Result\FacetSet;
use Solarium\Component\Result\Facet\Field as FacetField;
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
        $resultStub = $this->createStub(Result::class);

        $this->solrClientServiceMock
            ->expects(self::once())
            ->method('search')
            ->with($query, $offset, $limit, $filters)
            ->willReturn($resultStub);

        $resultStub->method('getDocuments')->willReturn([]);
        $resultStub->method('getNumFound')->willReturn(0);

        $results = $this->solrSearchEngine->search($query, $offset, $limit, $filters);

        self::assertEmpty($results->getDocuments());
        self::assertEquals(0, $results->getTotalCount());
        self::assertEquals($offset, $results->getOffset());
        self::assertEquals($limit, $results->getLimit());
    }
    public function testSearchMapsFacets(): void
    {
        $query = 'test';
        $resultStub = $this->createMock(Result::class);
        $facetSetStub = $this->createMock(FacetSet::class);
        $facetFieldStub = $this->createMock(FacetField::class);

        $this->solrClientServiceMock
            ->method('search')
            ->willReturn($resultStub);

        $resultStub->method('getIterator')->willReturn(new ArrayIterator([]));
        $resultStub->method('getNumFound')->willReturn(0);
        $resultStub->method('getFacetSet')->willReturn($facetSetStub);

        $facetSetStub->method('getIterator')->willReturn(new ArrayIterator([
            'language' => $facetFieldStub,
        ]));

        $facetFieldStub->method('getIterator')->willReturn(new ArrayIterator([
            'fr' => 5,
            'en' => 2,
        ]));

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

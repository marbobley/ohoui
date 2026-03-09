<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Solr;

use App\Domain\Model\Document;
use App\Infrastructure\Solr\SolrSearchEngine;
use App\Service\SolrClientServiceInterface;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Select\Result\Result;

class SolrSearchEngineTest extends TestCase
{
    private $solrClientServiceMock;
    private $solrSearchEngine;

    protected function setUp(): void
    {
        $this->solrClientServiceMock = $this->createMock(SolrClientServiceInterface::class);
        $this->solrSearchEngine = new SolrSearchEngine($this->solrClientServiceMock);
    }

    public function testIndex(): void
    {
        $document = new Document(
            '1',
            'Test Title',
            'https://test.com',
            'Test Content',
            'fr',
            'test.com'
        );

        $this->solrClientServiceMock->expects($this->once())
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

    public function testSearch(): void
    {
        $query = 'test';
        $resultMock = $this->createMock(Result::class);

        $this->solrClientServiceMock->expects($this->once())
            ->method('search')
            ->with($query)
            ->willReturn($resultMock);

        // Simulation d'un itérateur vide pour les résultats
        $resultMock->method('getIterator')->willReturn(new \ArrayIterator([]));

        $results = $this->solrSearchEngine->search($query);

        $this->assertIsArray($results);
    }
}

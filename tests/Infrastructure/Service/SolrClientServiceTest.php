<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Infrastructure\Solr\SolrDataMapperInterface;
use App\Infrastructure\Solr\SolrQueryBuilderInterface;
use App\Infrastructure\Solr\SolrResponse;
use App\Service\SolrClientService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\QueryType\Select\Query\Query as SelectQuery;
use Solarium\QueryType\Select\Result\Result as SelectResult;
use Solarium\QueryType\Update\Query\Document;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;

final class SolrClientServiceTest extends TestCase
{
    private SolrClientService $service;
    private Client&MockObject $clientMock;
    private SolrQueryBuilderInterface&MockObject $queryBuilderMock;
    private SolrDataMapperInterface&MockObject $dataMapperMock;

    protected function setUp(): void
    {
        $this->clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilderMock = $this->getMockBuilder(SolrQueryBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataMapperMock = $this->getMockBuilder(SolrDataMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->service = new SolrClientService($this->clientMock, $this->queryBuilderMock, $this->dataMapperMock);
    }

    public function testSearchCallsClientSelect(): void
    {
        $queryStr = 'test query';

        $selectMock = $this->createMock(SelectQuery::class);
        $resultStub = $this->createStub(SelectResult::class);
        $responseStub = $this->createStub(SolrResponse::class);

        $this->clientMock
            ->expects(self::once())
            ->method('createSelect')
            ->willReturn($selectMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('build')
            ->with($selectMock, $queryStr, 10, 20, []);

        $this->clientMock
            ->expects(self::once())
            ->method('select')
            ->with($selectMock)
            ->willReturn($resultStub);

        $this->dataMapperMock
            ->expects(self::once())
            ->method('mapResponse')
            ->with($resultStub)
            ->willReturn($responseStub);

        $result = $this->service->search($queryStr, 10, 20);
        self::assertSame($responseStub, $result);
    }

    public function testIndexDocumentCallsClientUpdate(): void
    {
        $data = ['id' => '1', 'title' => 'Test'];
        $updateMock = $this->createMock(UpdateQuery::class);
        $docMock = $this->createMock(Document::class);

        $this->clientMock
            ->expects(self::once())
            ->method('createUpdate')
            ->willReturn($updateMock);

        $updateMock->expects(self::once())->method('createDocument')->willReturn($docMock);

        $docMock->expects(self::exactly(2))->method('setField');

        $updateMock->expects(self::once())->method('addDocument')->with($docMock);

        $updateMock->expects(self::once())->method('addCommit');

        $this->clientMock
            ->expects(self::once())
            ->method('update')
            ->with($updateMock);

        $this->service->indexDocument($data);
    }
}

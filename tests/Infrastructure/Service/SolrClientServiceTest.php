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
use Solarium\QueryType\Server\CoreAdmin\Query\Query as CoreAdminQuery;
use Solarium\QueryType\Server\CoreAdmin\Query\Action\Status as CoreAdminStatus;
use Solarium\QueryType\Server\CoreAdmin\Result\Result as CoreAdminResult;
use Solarium\QueryType\Server\CoreAdmin\Result\StatusResult as CoreAdminStatusResult;

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

    public function testGetStatus(): void
    {
        $adminQueryMock = $this->createMock(CoreAdminQuery::class);
        $statusActionMock = $this->createMock(CoreAdminStatus::class);
        $adminResultMock = $this->createMock(CoreAdminResult::class);
        $coreStatusResultMock = $this->createMock(CoreAdminStatusResult::class);
        $selectQueryMock = $this->createMock(SelectQuery::class);
        $selectResultMock = $this->createMock(SelectResult::class);

        $coreName = 'test_core';
        $lastModified = new \DateTime('2023-10-27 10:00:00');

        $this->clientMock->expects(self::once())
            ->method('createCoreAdmin')
            ->willReturn($adminQueryMock);

        $this->clientMock->expects(self::once())
            ->method('getOptions')
            ->willReturn(['endpoint' => ['main' => ['core' => $coreName]]]);

        $adminQueryMock->expects(self::once())
            ->method('createStatus')
            ->willReturn($statusActionMock);

        $statusActionMock->expects(self::once())
            ->method('setCore')
            ->with($coreName);

        $adminQueryMock->expects(self::once())
            ->method('setAction')
            ->with($statusActionMock);

        $this->clientMock->expects(self::once())
            ->method('coreAdmin')
            ->with($adminQueryMock)
            ->willReturn($adminResultMock);

        $adminResultMock->expects(self::once())
            ->method('getStatusResult')
            ->willReturn($coreStatusResultMock);

        $coreStatusResultMock->expects(self::once())
            ->method('getLastModified')
            ->willReturn($lastModified);

        $coreStatusResultMock->expects(self::once())
            ->method('getUptime')
            ->willReturn(12345);

        $this->clientMock->expects(self::once())
            ->method('createSelect')
            ->willReturn($selectQueryMock);

        $selectQueryMock->expects(self::once())
            ->method('setRows')
            ->with(0);

        $this->clientMock->expects(self::once())
            ->method('select')
            ->with($selectQueryMock)
            ->willReturn($selectResultMock);

        $selectResultMock->expects(self::once())
            ->method('getNumFound')
            ->willReturn(128);

        $status = $this->service->getStatus();

        self::assertEquals([
            'core_name' => $coreName,
            'num_docs' => 128,
            'index_size' => 'N/A',
            'last_modified' => '2023-10-27 10:00:00',
            'uptime' => 12345,
        ], $status);
    }

    public function testSearchCallsClientSelect(): void
    {
        $queryStr = 'test query';

        $selectMock = $this->createMock(SelectQuery::class);
        $resultStub = $this->createStub(SelectResult::class);
        $responseStub = new SolrResponse([], 0);

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

    public function testPurgeCallsClientUpdateWithDeleteQuery(): void
    {
        $updateMock = $this->createMock(UpdateQuery::class);

        $this->clientMock
            ->expects(self::once())
            ->method('createUpdate')
            ->willReturn($updateMock);

        $updateMock->expects(self::once())->method('addDeleteQuery')->with('*:*');
        $updateMock->expects(self::once())->method('addCommit');

        $this->clientMock
            ->expects(self::once())
            ->method('update')
            ->with($updateMock);

        $this->service->purge();
    }
}

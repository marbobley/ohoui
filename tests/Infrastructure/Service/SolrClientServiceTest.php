<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Service\SolrClientService;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\Core\Client\Adapter\AdapterInterface;
use Solarium\QueryType\Select\Query\Query as SelectQuery;
use Solarium\QueryType\Select\Result\Result as SelectResult;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SolrClientServiceTest extends TestCase
{
    private SolrClientService $service;
    private Client $clientMock;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(Client::class);
        $adapterStub = $this->createStub(AdapterInterface::class);
        $eventDispatcherStub = $this->createStub(EventDispatcherInterface::class);

        $this->service = new SolrClientService($adapterStub, $eventDispatcherStub, 'localhost', 8983, '/', 'test_core');

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('client');
        $property->setValue($this->service, $this->clientMock);
    }

    public function testSearchCallsClientSelect(): void
    {
        $queryStr = 'test query';
        $escapedQuery = 'test\ query';
        $formattedQuery = 'title:test\ query OR content:test\ query';

        $selectMock = $this->createMock(SelectQuery::class);
        $helperMock = $this->createMock(\Solarium\Core\Query\Helper::class);
        $resultStub = $this->createStub(SelectResult::class);

        $this->clientMock
            ->expects($this->once())
            ->method('createSelect')
            ->willReturn($selectMock);

        $selectMock->expects($this->once())->method('getHelper')->willReturn($helperMock);

        $helperMock->expects($this->once())->method('escapeTerm')->with($queryStr)->willReturn($escapedQuery);

        $selectMock->expects($this->once())->method('setQuery')->with($formattedQuery);

        $this->clientMock
            ->expects($this->once())
            ->method('select')
            ->with($selectMock)
            ->willReturn($resultStub);

        $result = $this->service->search($queryStr);
        static::assertSame($resultStub, $result);
    }

    public function testSearchWithExplicitFieldDoesNotFormatQuery(): void
    {
        $queryStr = 'title:specific';
        $selectMock = $this->createMock(SelectQuery::class);
        $resultStub = $this->createStub(SelectResult::class);

        $this->clientMock
            ->expects($this->once())
            ->method('createSelect')
            ->willReturn($selectMock);

        $selectMock->expects($this->never())->method('getHelper');

        $selectMock->expects($this->once())->method('setQuery')->with($queryStr);

        $this->clientMock
            ->expects($this->once())
            ->method('select')
            ->with($selectMock)
            ->willReturn($resultStub);

        $result = $this->service->search($queryStr);
        static::assertSame($resultStub, $result);
    }

    public function testIndexDocumentCallsClientUpdate(): void
    {
        $data = ['id' => '1', 'title' => 'Test'];
        $updateMock = $this->createMock(UpdateQuery::class);
        $docMock = $this->createMock(\Solarium\QueryType\Update\Query\Document::class);

        $this->clientMock
            ->expects($this->once())
            ->method('createUpdate')
            ->willReturn($updateMock);

        $updateMock->expects($this->once())->method('createDocument')->willReturn($docMock);

        $docMock->expects($this->exactly(2))->method('setField');

        $updateMock->expects($this->once())->method('addDocument')->with($docMock);

        $updateMock->expects($this->once())->method('addCommit');

        $this->clientMock
            ->expects($this->once())
            ->method('update')
            ->with($updateMock);

        $this->service->indexDocument($data);
    }
}

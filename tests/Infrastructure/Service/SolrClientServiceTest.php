<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Service\SolrClientService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\Component\FacetSet;
use Solarium\Component\Facet\Field as FacetField;
use Solarium\Core\Query\Helper;
use Solarium\QueryType\Select\Query\FilterQuery;
use Solarium\QueryType\Select\Query\Query as SelectQuery;
use Solarium\QueryType\Select\Result\Result as SelectResult;
use Solarium\QueryType\Update\Query\Document;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;

final class SolrClientServiceTest extends TestCase
{
    private SolrClientService $service;
    private Client&MockObject $clientMock;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(Client::class);
        $this->service = new SolrClientService($this->clientMock);
    }

    public function testSearchCallsClientSelect(): void
    {
        $queryStr = 'test query';
        $escapedQuery = 'test\ query';
        $formattedQuery = 'title:"test\ query"^2.0 OR content:"test\ query"';

        $selectMock = $this->createMock(SelectQuery::class);
        $helperMock = $this->createMock(Helper::class);
        $resultStub = $this->createStub(SelectResult::class);
        $facetSetMock = $this->createMock(FacetSet::class);
        $facetFieldMock = $this->createMock(FacetField::class);

        $this->clientMock
            ->expects(self::once())
            ->method('createSelect')
            ->willReturn($selectMock);

        $selectMock->expects(self::once())->method('getFacetSet')->willReturn($facetSetMock);
        $facetSetMock->expects(self::exactly(2))->method('createFacetField')->willReturn($facetFieldMock);
        $facetFieldMock->expects(self::exactly(2))->method('setField');

        $selectMock->expects(self::once())->method('getHelper')->willReturn($helperMock);

        $helperMock->expects(self::once())->method('escapeTerm')->with($queryStr)->willReturn($escapedQuery);

        $selectMock->expects(self::once())->method('setQuery')->with($formattedQuery);
        $selectMock->expects(self::once())->method('setStart')->with(10);
        $selectMock->expects(self::once())->method('setRows')->with(20);

        $this->clientMock
            ->expects(self::once())
            ->method('select')
            ->with($selectMock)
            ->willReturn($resultStub);

        $result = $this->service->search($queryStr, 10, 20);
        self::assertSame($resultStub, $result);
    }

    public function testSearchWithExplicitFieldDoesNotFormatQuery(): void
    {
        $queryStr = 'title:specific';
        $selectMock = $this->createMock(SelectQuery::class);
        $resultStub = $this->createStub(SelectResult::class);
        $facetSetMock = $this->createMock(FacetSet::class);
        $facetFieldMock = $this->createMock(FacetField::class);

        $this->clientMock
            ->expects(self::once())
            ->method('createSelect')
            ->willReturn($selectMock);

        $selectMock->expects(self::once())->method('getFacetSet')->willReturn($facetSetMock);
        $facetSetMock->expects(self::exactly(2))->method('createFacetField')->willReturn($facetFieldMock);
        $facetFieldMock->expects(self::exactly(2))->method('setField');

        $selectMock->expects(self::never())->method('getHelper');

        $selectMock->expects(self::once())->method('setQuery')->with($queryStr);

        $this->clientMock
            ->expects(self::once())
            ->method('select')
            ->with($selectMock)
            ->willReturn($resultStub);

        $result = $this->service->search($queryStr);
        self::assertSame($resultStub, $result);
    }

    public function testSearchWithFilters(): void
    {
        $queryStr = 'test';
        $filters = ['language' => 'fr', 'domain' => 'example.com'];
        $selectMock = $this->createMock(SelectQuery::class);
        $resultStub = $this->createMock(SelectResult::class);
        $facetSetMock = $this->createMock(FacetSet::class);
        $facetFieldMock = $this->createMock(FacetField::class);
        $filterQueryMock = $this->createMock(FilterQuery::class);

        $this->clientMock
            ->expects(self::once())
            ->method('createSelect')
            ->willReturn($selectMock);

        $selectMock->method('getFacetSet')->willReturn($facetSetMock);
        $facetSetMock->method('createFacetField')->willReturn($facetFieldMock);

        $selectMock->method('getHelper')->willReturn($this->createMock(Helper::class));

        $selectMock->expects(self::exactly(2))
            ->method('createFilterQuery')
            ->willReturn($filterQueryMock);

        $filterQueryMock->expects(self::exactly(2))
            ->method('setQuery');

        $this->clientMock
            ->expects(self::once())
            ->method('select')
            ->willReturn($resultStub);

        $result = $this->service->search($queryStr, 0, 10, $filters);
        self::assertSame($resultStub, $result);
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

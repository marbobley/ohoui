<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Solr;

use App\Infrastructure\Solr\SolrQueryBuilder;
use PHPUnit\Framework\TestCase;
use Solarium\Core\Query\Helper;
use Solarium\QueryType\Select\Query\Query as SelectQuery;

use function PHPUnit\Framework\self;
use function PHPUnit\Framework\staticExpects;

final class SolrQueryBuilderTest extends TestCase
{
    private SolrQueryBuilder $builder;
    private SelectQuery|\PHPUnit\Framework\MockObject\MockObject $selectMock;
    private Helper|\PHPUnit\Framework\MockObject\MockObject $helperMock;

    #[Override]
    protected function setUp(): void
    {
        $this->builder = new SolrQueryBuilder();
        $this->selectMock = $this->createMock(SelectQuery::class);
        $this->helperMock = $this->createMock(Helper::class);
    }

    public function testBuildStandardQuery(): void
    {
        $queryText = 'symfony';
        $start = 10;
        $rows = 20;
        $filters = [];

        $this->selectMock->expects(self::once())
            ->method('getHelper')
            ->willReturn($this->helperMock);

        $this->helperMock->expects(self::once())
            ->method('escapeTerm')
            ->with($queryText)
            ->willReturn('symfony');

        $this->selectMock->expects(self::once())
            ->method('setQuery')
            ->with('title:"symfony"^2.0 OR content:"symfony"');

        $this->selectMock->expects(self::once())
            ->method('setStart')
            ->with($start);

        $this->selectMock->expects(self::once())
            ->method('setRows')
            ->with($rows);

        $this->builder->build($this->selectMock, $queryText, $start, $rows, $filters);
    }

    public function testBuildAdvancedQuery(): void
    {
        // Une requête contenant déjà ":" ne devrait pas être altérée par le boost par défaut
        $queryText = 'id:123';
        $start = 0;
        $rows = 10;
        $filters = [];

        $this->selectMock->expects(self::once())
            ->method('getHelper')
            ->willReturn($this->helperMock);

        $this->selectMock->expects(self::once())
            ->method('setQuery')
            ->with('id:123');

        $this->builder->build($this->selectMock, $queryText, $start, $rows, $filters);
    }

    public function testBuildWithFilters(): void
    {
        $queryText = 'test';
        $start = 0;
        $rows = 10;
        $filters = ['language' => 'fr', 'domain' => 'example.com'];

        $this->selectMock->expects(self::once())
            ->method('getHelper')
            ->willReturn($this->helperMock);

        $this->helperMock->method('escapeTerm')->willReturnCallback(function ($term) {
            return $term;
        });

        // On s'attend à ce que deux FilterQuery soient créés
        $filterQueryMock1 = $this->createMock(\Solarium\QueryType\Select\Query\FilterQuery::class);
        $filterQueryMock2 = $this->createMock(\Solarium\QueryType\Select\Query\FilterQuery::class);

        $this->selectMock->expects(self::exactly(2))
            ->method('createFilterQuery')
            ->willReturnCallback(function (string $field) use ($filterQueryMock1, $filterQueryMock2) {
                if ($field === 'language') {
                    return $filterQueryMock1;
                }
                if ($field === 'domain') {
                    return $filterQueryMock2;
                }
                throw new \InvalidArgumentException("Unexpected field: $field");
            });

        $filterQueryMock1->expects(self::once())
            ->method('setQuery')
            ->with('language:fr');

        $filterQueryMock2->expects(self::once())
            ->method('setQuery')
            ->with('domain:example.com');

        $this->builder->build($this->selectMock, $queryText, $start, $rows, $filters);
    }
}

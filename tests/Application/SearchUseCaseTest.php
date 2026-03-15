<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Service\SearchUseCase;
use App\Domain\Model\SearchCriteria;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;
use App\Tests\Util\DocumentFactory;
use PHPUnit\Framework\TestCase;

class SearchUseCaseTest extends TestCase
{
    public function testExecuteDelegatesToSearchEngine(): void
    {
        $query = 'test';
        $page = 3;
        $limit = 10;
        $filters = ['language' => 'fr'];
        $offset = 20;

        $criteria = new SearchCriteria($query, $page, $limit, $filters);

        $documents = [
            DocumentFactory::create(highlight: '<em>test</em>'),
        ];
        $expectedResults = new SearchResult(
            documents: $documents,
            totalCount: 1,
            limit: $limit,
            offset: $offset
        );

        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects($this->once())
            ->method('search')
            ->with($criteria)
            ->willReturn($expectedResults);

        $useCase = new SearchUseCase($searchEngineMock);
        $results = $useCase->execute($criteria);

        static::assertSame($expectedResults, $results);
        static::assertSame('<em>test</em>', $results->getDocuments()[0]->getHighlight());
    }
}
